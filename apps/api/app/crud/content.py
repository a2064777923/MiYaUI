from __future__ import annotations

from typing import Any

from sqlalchemy import delete, func, or_, select
from sqlalchemy.ext.asyncio import AsyncSession

from app.crud.common import upsert_by_source
from app.models.content import ContentItem, ContentMeta, ContentTerm, Taxonomy, Term
from app.models.media import MediaAsset


async def upsert_content_item(session: AsyncSession, payload: dict[str, Any]) -> tuple[ContentItem, bool]:
    instance, created = await upsert_by_source(session, ContentItem, payload)
    return instance, created  # type: ignore[return-value]


async def replace_content_meta(
    session: AsyncSession,
    content_item: ContentItem,
    metas: list[dict[str, Any]],
) -> None:
    await session.execute(delete(ContentMeta).where(ContentMeta.content_item_id == content_item.id))
    for meta in metas:
        session.add(
            ContentMeta(
                content_item_id=content_item.id,
                meta_key=meta["meta_key"],
                raw_value=meta.get("raw_value"),
                parsed_value=meta.get("parsed_value", {}),
                source_system=meta.get("source_system", "wordpress"),
                source_table=meta["source_table"],
                source_id=meta["source_id"],
                source_updated_at=meta.get("source_updated_at"),
            )
        )
    await session.flush()


async def upsert_term_with_taxonomy(
    session: AsyncSession,
    term_payload: dict[str, Any],
) -> tuple[Taxonomy, Term]:
    taxonomy, _ = await upsert_by_source(session, Taxonomy, term_payload["taxonomy"])
    term, _ = await upsert_by_source(session, Term, term_payload["term"])
    return taxonomy, term  # type: ignore[return-value]


async def replace_content_terms(
    session: AsyncSession,
    content_item: ContentItem,
    term_payloads: list[dict[str, Any]],
) -> None:
    await session.execute(delete(ContentTerm).where(ContentTerm.content_item_id == content_item.id))
    for payload in term_payloads:
        taxonomy, term = await upsert_term_with_taxonomy(session, payload)
        session.add(
            ContentTerm(
                content_item_id=content_item.id,
                taxonomy_id=taxonomy.id,
                term_id=term.id,
                source_system="wordpress",
                source_table=payload["source_table"],
                source_id=payload["source_id"],
                source_updated_at=payload.get("source_updated_at"),
            )
        )
    await session.flush()


async def get_content_item_by_source_id(session: AsyncSession, source_id: str) -> ContentItem | None:
    result = await session.execute(select(ContentItem).where(ContentItem.source_id == source_id))
    return result.scalar_one_or_none()


async def get_content_item_by_id(session: AsyncSession, item_id: int) -> ContentItem | None:
    result = await session.execute(select(ContentItem).where(ContentItem.id == item_id))
    return result.scalar_one_or_none()


async def get_content_item_by_original_path(session: AsyncSession, original_url_path: str) -> ContentItem | None:
    result = await session.execute(
        select(ContentItem).where(
            ContentItem.original_url_path == original_url_path,
            ContentItem.status == "publish",
        )
    )
    return result.scalar_one_or_none()


async def list_content_items(
    session: AsyncSession,
    *,
    page: int = 1,
    page_size: int = 20,
    content_type: str | None = None,
    search: str | None = None,
) -> tuple[list[ContentItem], int]:
    filters = [ContentItem.status == "publish"]
    if content_type:
        filters.append(ContentItem.content_type == content_type)
    if search:
        like_value = f"%{search}%"
        filters.append(
            or_(
                ContentItem.title.ilike(like_value),
                ContentItem.search_text.ilike(like_value),
                ContentItem.excerpt.ilike(like_value),
            )
        )
    count_result = await session.execute(select(func.count()).select_from(ContentItem).where(*filters))
    total = int(count_result.scalar() or 0)
    result = await session.execute(
        select(ContentItem)
        .where(*filters)
        .order_by(ContentItem.published_at.desc(), ContentItem.id.desc())
        .offset((page - 1) * page_size)
        .limit(page_size)
    )
    return list(result.scalars().all()), total


async def list_media_assets_for_items(
    session: AsyncSession,
    content_item_ids: list[int],
) -> dict[int, list[MediaAsset]]:
    if not content_item_ids:
        return {}
    result = await session.execute(
        select(MediaAsset).where(MediaAsset.content_item_id.in_(content_item_ids))
    )
    media_map: dict[int, list[MediaAsset]] = {item_id: [] for item_id in content_item_ids}
    for asset in result.scalars().all():
        if asset.content_item_id is not None:
            media_map.setdefault(asset.content_item_id, []).append(asset)
    return media_map


async def get_taxonomy_listing(
    session: AsyncSession,
    *,
    taxonomy_name: str,
    slug: str,
    page: int = 1,
    page_size: int = 20,
) -> tuple[Taxonomy | None, Term | None, list[ContentItem], int]:
    result = await session.execute(
        select(Taxonomy, Term)
        .join(ContentTerm, ContentTerm.taxonomy_id == Taxonomy.id)
        .join(Term, Term.id == ContentTerm.term_id)
        .where(Taxonomy.taxonomy == taxonomy_name, Term.slug == slug)
        .limit(1)
    )
    row = result.first()
    if row is None:
        return None, None, [], 0
    taxonomy, term = row
    filters = [
        ContentTerm.taxonomy_id == taxonomy.id,
        ContentTerm.term_id == term.id,
        ContentItem.status == "publish",
    ]
    count_result = await session.execute(
        select(func.count())
        .select_from(ContentTerm)
        .join(ContentItem, ContentItem.id == ContentTerm.content_item_id)
        .where(*filters)
    )
    total = int(count_result.scalar() or 0)
    items_result = await session.execute(
        select(ContentItem)
        .join(ContentTerm, ContentItem.id == ContentTerm.content_item_id)
        .where(*filters)
        .order_by(ContentItem.published_at.desc(), ContentItem.id.desc())
        .offset((page - 1) * page_size)
        .limit(page_size)
    )
    return taxonomy, term, list(items_result.scalars().all()), total


async def list_content_by_author_source_id(
    session: AsyncSession,
    *,
    author_source_id: str,
    page: int = 1,
    page_size: int = 20,
) -> tuple[list[ContentItem], int]:
    filters = [
        ContentItem.author_source_id == author_source_id,
        ContentItem.status == "publish",
    ]
    count_result = await session.execute(select(func.count()).select_from(ContentItem).where(*filters))
    total = int(count_result.scalar() or 0)
    result = await session.execute(
        select(ContentItem)
        .where(*filters)
        .order_by(ContentItem.published_at.desc(), ContentItem.id.desc())
        .offset((page - 1) * page_size)
        .limit(page_size)
    )
    return list(result.scalars().all()), total


async def list_sitemap_entries(session: AsyncSession) -> list[ContentItem]:
    result = await session.execute(
        select(ContentItem)
        .where(ContentItem.status == "publish")
        .order_by(ContentItem.updated_at.desc(), ContentItem.id.desc())
    )
    return list(result.scalars().all())
