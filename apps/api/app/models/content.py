from __future__ import annotations

from datetime import datetime
from typing import Any

from sqlalchemy import JSON, Column, Index, Text, UniqueConstraint
from sqlmodel import Field, SQLModel

from app.models.base import SourceTraceMixin, TimestampMixin


class ContentItem(TimestampMixin, SourceTraceMixin, table=True):
    __tablename__ = "content_items"
    __table_args__ = (
        UniqueConstraint("source_table", "source_id", name="uq_content_items_source"),
        UniqueConstraint("original_url_path", name="uq_content_items_original_url_path"),
        Index("ix_content_items_type_status_published", "content_type", "status", "published_at"),
    )

    id: int | None = Field(default=None, primary_key=True)
    wordpress_post_id: int | None = Field(default=None, index=True)
    author_source_id: str | None = Field(default=None, max_length=191, index=True)
    content_type: str = Field(max_length=64, index=True)
    status: str = Field(max_length=32, index=True)
    slug: str = Field(default="", max_length=255, index=True)
    title: str = Field(default="", max_length=500)
    body: str = Field(default="", sa_column=Column(Text, nullable=False))
    excerpt: str = Field(default="", sa_column=Column(Text, nullable=False))
    published_at: datetime | None = Field(default=None, index=True)
    content_updated_at: datetime | None = Field(default=None, index=True)
    original_url_path: str = Field(max_length=500, index=True)
    seo_title: str | None = Field(default=None, max_length=500)
    seo_description: str | None = Field(default=None, max_length=1000)
    seo_json: dict[str, Any] = Field(default_factory=dict, sa_column=Column(JSON, nullable=False))
    meta_json: dict[str, Any] = Field(default_factory=dict, sa_column=Column(JSON, nullable=False))
    search_text: str = Field(default="", sa_column=Column(Text, nullable=False))
    render_mode: str = Field(default="ssr", max_length=32)
    fallback_required: bool = Field(default=False, index=True)


class ContentMeta(TimestampMixin, SourceTraceMixin, table=True):
    __tablename__ = "content_meta"
    __table_args__ = (
        UniqueConstraint("source_table", "source_id", name="uq_content_meta_source"),
        Index("ix_content_meta_item_key", "content_item_id", "meta_key"),
    )

    id: int | None = Field(default=None, primary_key=True)
    content_item_id: int | None = Field(default=None, foreign_key="content_items.id", index=True)
    meta_key: str = Field(max_length=191, index=True)
    raw_value: str | None = Field(default=None, sa_column=Column(Text, nullable=True))
    parsed_value: dict[str, Any] = Field(default_factory=dict, sa_column=Column(JSON, nullable=False))


class Taxonomy(TimestampMixin, SourceTraceMixin, table=True):
    __tablename__ = "taxonomies"
    __table_args__ = (UniqueConstraint("source_table", "source_id", name="uq_taxonomies_source"),)

    id: int | None = Field(default=None, primary_key=True)
    taxonomy: str = Field(max_length=128, index=True)
    description: str = Field(default="", sa_column=Column(Text, nullable=False))
    parent_source_id: str | None = Field(default=None, max_length=191, index=True)
    count: int = Field(default=0)


class Term(TimestampMixin, SourceTraceMixin, table=True):
    __tablename__ = "terms"
    __table_args__ = (
        UniqueConstraint("source_table", "source_id", name="uq_terms_source"),
    )

    id: int | None = Field(default=None, primary_key=True)
    slug: str = Field(max_length=255, index=True)
    name: str = Field(max_length=255, index=True)
    description: str = Field(default="", sa_column=Column(Text, nullable=False))


class ContentTerm(TimestampMixin, SourceTraceMixin, table=True):
    __tablename__ = "content_terms"
    __table_args__ = (
        UniqueConstraint("source_table", "source_id", name="uq_content_terms_source"),
        UniqueConstraint("content_item_id", "taxonomy_id", "term_id", name="uq_content_terms_link"),
    )

    id: int | None = Field(default=None, primary_key=True)
    content_item_id: int = Field(foreign_key="content_items.id", index=True)
    taxonomy_id: int = Field(foreign_key="taxonomies.id", index=True)
    term_id: int = Field(foreign_key="terms.id", index=True)


CONTENT_MODELS: tuple[type[SQLModel], ...] = (ContentItem, ContentMeta, Taxonomy, Term, ContentTerm)
