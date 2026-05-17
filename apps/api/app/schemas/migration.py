from __future__ import annotations

from datetime import datetime
from typing import Any

from pydantic import BaseModel, Field


class SourceRef(BaseModel):
    source_system: str = "wordpress"
    source_table: str
    source_id: str
    source_updated_at: datetime | None = None


class ParsedMetaValue(BaseModel):
    status: str
    is_serialized: bool
    raw: str | None = None
    value: Any = None


class MigrationRunRead(BaseModel):
    id: int
    dataset: str
    mode: str
    status: str
    dry_run: bool = False
    started_at: datetime
    completed_at: datetime | None = None
    records_read: int = 0
    records_written: int = 0
    records_failed: int = 0
    notes: str = ""


class ReconciliationSummary(BaseModel):
    dataset: str
    source_count: int
    target_count: int
    sample_size: int = 0
    mismatch_count: int = 0
    matched: bool
    details: dict[str, Any] = Field(default_factory=dict)
