from __future__ import annotations

from fastapi import APIRouter, Depends, Query
from sqlalchemy.ext.asyncio import AsyncSession

from app.core.database import get_db
from app.crud.content import (
    get_content_item_by_id,
    get_content_item_by_original_path,
    get_taxonomy_listing,
    list_content_by_author_source_id,
    list_content_items,
    list_media_assets_for_items,
    list_sitemap_entries,
)
from app.crud.users import get_user_by_nicename
from app.models.content import ContentItem
from app.models.media import MediaAsset
from app.schemas.content import (
    AuthorRead,
    ContentDetail,
    ContentListResponse,
    ContentRouteResponse,
    ContentSummary,
    SitemapEntry,
    TaxonomyRead,
    TermRead,
)
from app.services.content import build_content_excerpt

router = APIRouter(prefix="/content", tags=["content"])


def _summary_from_model(item: ContentItem) -> ContentSummary:
    return ContentSummary.model_validate(item, from_attributes=True)


def _author_read(user) -> AuthorRead:
    return AuthorRead.model_validate(user, from_attributes=True)


def _media_read(asset: MediaAsset) -> dict[str, object]:
    return {
        "id": asset.id,
        "source_url": asset.source_url,
        "source_path": asset.source_path,
        "mime_type": asset.mime_type,
        "title": asset.title,
        "alt_text": asset.alt_text,
        "width": asset.width,
        "height": asset.height,
    }


async def _detail_from_model(db: AsyncSession, item: ContentItem) -> ContentDetail:
    author = None
    if item.author_source_id:
        author_nicename = item.meta_json.get("author_nicename")
        if author_nicename:
            author = await get_user_by_nicename(db, author_nicename)
    media_map = await list_media_assets_for_items(db, [item.id])
    detail_payload = ContentDetail.model_validate(item, from_attributes=True).model_dump(
        exclude={"media", "author"}
    )
    return ContentDetail(
        **detail_payload,
        media=[_media_read(asset) for asset in media_map.get(item.id, [])],
        author=_author_read(author) if author else None,
    )


@router.get("/resolve", response_model=ContentRouteResponse)
async def resolve_content_path(
    path: str = Query(..., description="Current route path, such as /123.html"),
    page: int = Query(1, ge=1),
    page_size: int = Query(20, ge=1, le=100),
    q: str | None = Query(default=None),
    db: AsyncSession = Depends(get_db),
) -> ContentRouteResponse:
    if path == "/":
        items, _ = await list_content_items(db, page=page, page_size=page_size)
        return ContentRouteResponse(
            status="ok",
            view="home",
            items=[_summary_from_model(item) for item in items],
            title="MiyaUI",
            description="Latest migrated content",
        )

    content_item = await get_content_item_by_original_path(db, path)
    if content_item is not None:
        detail = await _detail_from_model(db, content_item)
        return ContentRouteResponse(
            status="ok",
            view="detail",
            item=detail,
            title=detail.seo_title or detail.title,
            description=detail.seo_description or build_content_excerpt(detail.body, detail.excerpt),
        )

    if path.startswith("/category/") or path.startswith("/tag/"):
        parts = [part for part in path.strip("/").split("/") if part]
        if len(parts) >= 2:
            taxonomy_name, slug = parts[0], parts[1]
            taxonomy, term, items, _ = await get_taxonomy_listing(
                db,
                taxonomy_name=taxonomy_name,
                slug=slug,
                page=page,
                page_size=page_size,
            )
            if taxonomy is not None and term is not None:
                return ContentRouteResponse(
                    status="ok",
                    view="taxonomy",
                    items=[_summary_from_model(item) for item in items],
                    taxonomy=TaxonomyRead.model_validate(taxonomy, from_attributes=True),
                    term=TermRead.model_validate(term, from_attributes=True),
                    title=f"{term.name} | MiyaUI",
                    description=taxonomy.description or f"{taxonomy.taxonomy} archive for {term.name}",
                )

    if path.startswith("/author/"):
        parts = [part for part in path.strip("/").split("/") if part]
        if len(parts) >= 2:
            nicename = parts[1]
            author = await get_user_by_nicename(db, nicename)
            if author is not None:
                author_source_id = str(author.wordpress_user_id or author.id)
                items, _ = await list_content_by_author_source_id(
                    db,
                    author_source_id=author_source_id,
                    page=page,
                    page_size=page_size,
                )
                return ContentRouteResponse(
                    status="ok",
                    view="author",
                    author=_author_read(author),
                    items=[_summary_from_model(item) for item in items],
                    title=f"{author.display_name} | MiyaUI",
                    description=f"Posts by {author.display_name}",
                )

    if path == "/search" and q:
        items, _ = await list_content_items(db, page=page, page_size=page_size, search=q)
        return ContentRouteResponse(
            status="ok",
            view="search",
            items=[_summary_from_model(item) for item in items],
            query=q,
            title=f"Search: {q}",
            description=f"Search results for {q}",
        )

    return ContentRouteResponse(status="not_migrated", view="fallback")


@router.get("/items", response_model=ContentListResponse)
async def get_content_items(
    page: int = Query(1, ge=1),
    page_size: int = Query(20, ge=1, le=100),
    content_type: str | None = Query(default=None),
    q: str | None = Query(default=None),
    db: AsyncSession = Depends(get_db),
) -> ContentListResponse:
    items, total = await list_content_items(
        db,
        page=page,
        page_size=page_size,
        content_type=content_type,
        search=q,
    )
    return ContentListResponse(
        items=[_summary_from_model(item) for item in items],
        total=total,
        page=page,
        page_size=page_size,
    )


@router.get("/items/{item_id}", response_model=ContentDetail | None)
async def get_content_item(item_id: int, db: AsyncSession = Depends(get_db)) -> ContentDetail | None:
    item = await get_content_item_by_id(db, item_id)
    if item is None:
        return None
    return await _detail_from_model(db, item)


@router.get("/taxonomies/{taxonomy}/{slug}", response_model=ContentRouteResponse)
async def get_taxonomy_content(
    taxonomy: str,
    slug: str,
    page: int = Query(1, ge=1),
    page_size: int = Query(20, ge=1, le=100),
    db: AsyncSession = Depends(get_db),
) -> ContentRouteResponse:
    taxonomy_row, term, items, _ = await get_taxonomy_listing(
        db,
        taxonomy_name=taxonomy,
        slug=slug,
        page=page,
        page_size=page_size,
    )
    if taxonomy_row is None or term is None:
        return ContentRouteResponse(status="not_migrated", view="fallback")
    return ContentRouteResponse(
        status="ok",
        view="taxonomy",
        items=[_summary_from_model(item) for item in items],
        taxonomy=TaxonomyRead.model_validate(taxonomy_row, from_attributes=True),
        term=TermRead.model_validate(term, from_attributes=True),
        title=f"{term.name} | MiyaUI",
        description=taxonomy_row.description or f"{taxonomy} archive",
    )


@router.get("/authors/{nicename}", response_model=ContentRouteResponse)
async def get_author_content(
    nicename: str,
    page: int = Query(1, ge=1),
    page_size: int = Query(20, ge=1, le=100),
    db: AsyncSession = Depends(get_db),
) -> ContentRouteResponse:
    author = await get_user_by_nicename(db, nicename)
    if author is None:
        return ContentRouteResponse(status="not_migrated", view="fallback")
    items, _ = await list_content_by_author_source_id(
        db,
        author_source_id=str(author.wordpress_user_id or author.id),
        page=page,
        page_size=page_size,
    )
    return ContentRouteResponse(
        status="ok",
        view="author",
        author=_author_read(author),
        items=[_summary_from_model(item) for item in items],
        title=f"{author.display_name} | MiyaUI",
        description=f"Posts by {author.display_name}",
    )


@router.get("/search", response_model=ContentListResponse)
async def search_content(
    q: str = Query(..., min_length=1),
    page: int = Query(1, ge=1),
    page_size: int = Query(20, ge=1, le=100),
    db: AsyncSession = Depends(get_db),
) -> ContentListResponse:
    items, total = await list_content_items(db, page=page, page_size=page_size, search=q)
    return ContentListResponse(
        items=[_summary_from_model(item) for item in items],
        total=total,
        page=page,
        page_size=page_size,
    )


@router.get("/sitemap", response_model=list[SitemapEntry])
async def get_sitemap(db: AsyncSession = Depends(get_db)) -> list[SitemapEntry]:
    items = await list_sitemap_entries(db)
    return [
        SitemapEntry(path=item.original_url_path, updated_at=item.content_updated_at or item.updated_at)
        for item in items
    ]
