from __future__ import annotations

from datetime import UTC, datetime
from typing import Any

from sqlalchemy import JSON, Column
from sqlmodel import Field, SQLModel


def utc_now() -> datetime:
    return datetime.now(UTC).replace(tzinfo=None)


def json_column(default_factory: type[dict[str, Any]] | type[list[Any]]):
    return Field(default_factory=default_factory, sa_column=Column(JSON, nullable=False))


class TimestampMixin(SQLModel):
    created_at: datetime = Field(default_factory=utc_now, nullable=False)
    updated_at: datetime = Field(
        default_factory=utc_now,
        nullable=False,
        sa_column_kwargs={"onupdate": utc_now},
    )


class SourceTraceMixin(SQLModel):
    source_system: str = Field(default="wordpress", max_length=64, index=True)
    source_table: str = Field(max_length=128, index=True)
    source_id: str = Field(max_length=191, index=True)
    source_updated_at: datetime | None = Field(default=None, index=True)
