from __future__ import annotations

from typing import Any

from sqlalchemy import JSON, Column, UniqueConstraint
from sqlmodel import Field, SQLModel

from app.models.base import SourceTraceMixin, TimestampMixin


class MediaAsset(TimestampMixin, SourceTraceMixin, table=True):
    __tablename__ = "media_assets"
    __table_args__ = (
        UniqueConstraint("source_table", "source_id", name="uq_media_assets_source"),
        UniqueConstraint("source_url", name="uq_media_assets_source_url"),
    )

    id: int | None = Field(default=None, primary_key=True)
    content_item_id: int | None = Field(default=None, foreign_key="content_items.id", index=True)
    attachment_source_id: str | None = Field(default=None, max_length=191, index=True)
    source_url: str = Field(max_length=1000, index=True)
    source_path: str | None = Field(default=None, max_length=1000)
    mime_type: str | None = Field(default=None, max_length=255)
    title: str = Field(default="", max_length=255)
    alt_text: str | None = Field(default=None, max_length=500)
    width: int | None = Field(default=None)
    height: int | None = Field(default=None)
    metadata_json: dict[str, Any] = Field(default_factory=dict, sa_column=Column(JSON, nullable=False))


MEDIA_MODELS: tuple[type[SQLModel], ...] = (MediaAsset,)
