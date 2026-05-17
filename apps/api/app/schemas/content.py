from __future__ import annotations

from datetime import datetime
from typing import Any

from pydantic import BaseModel, Field


class TermRead(BaseModel):
    id: int
    slug: str
    name: str


class TaxonomyRead(BaseModel):
    id: int
    taxonomy: str
    description: str = ""
    count: int = 0


class AuthorRead(BaseModel):
    id: int
    username: str
    nicename: str | None = None
    display_name: str = ""
    role_label: str = "member"


class MediaRead(BaseModel):
    id: int
    source_url: str
    source_path: str | None = None
    mime_type: str | None = None
    title: str = ""
    alt_text: str | None = None
    width: int | None = None
    height: int | None = None


class ContentSummary(BaseModel):
    id: int
    content_type: str
    status: str
    slug: str
    title: str
    excerpt: str = ""
    original_url_path: str
    published_at: datetime | None = None
    seo_title: str | None = None
    seo_description: str | None = None


class ContentDetail(ContentSummary):
    body: str = ""
    author_source_id: str | None = None
    seo_json: dict[str, Any] = Field(default_factory=dict)
    meta_json: dict[str, Any] = Field(default_factory=dict)
    fallback_required: bool = False
    media: list[MediaRead] = Field(default_factory=list)
    author: AuthorRead | None = None


class ContentRouteResponse(BaseModel):
    status: str
    view: str
    item: ContentDetail | None = None
    items: list[ContentSummary] = Field(default_factory=list)
    taxonomy: TaxonomyRead | None = None
    term: TermRead | None = None
    author: AuthorRead | None = None
    query: str | None = None
    title: str | None = None
    description: str | None = None
    fallback_url: str | None = None


class ContentListResponse(BaseModel):
    items: list[ContentSummary] = Field(default_factory=list)
    total: int = 0
    page: int = 1
    page_size: int = 20


class SitemapEntry(BaseModel):
    path: str
    updated_at: datetime | None = None
