from __future__ import annotations

from datetime import datetime
from typing import Any

from sqlalchemy import JSON, Column, Text, UniqueConstraint
from sqlmodel import Field, SQLModel

from app.models.base import TimestampMixin, utc_now


class MigrationRun(TimestampMixin, table=True):
    __tablename__ = "migration_runs"

    id: int | None = Field(default=None, primary_key=True)
    dataset: str = Field(max_length=128, index=True)
    mode: str = Field(default="backfill", max_length=32, index=True)
    status: str = Field(default="pending", max_length=32, index=True)
    dry_run: bool = Field(default=False, index=True)
    started_at: datetime = Field(default_factory=utc_now, nullable=False, index=True)
    completed_at: datetime | None = Field(default=None, index=True)
    records_read: int = Field(default=0)
    records_written: int = Field(default=0)
    records_failed: int = Field(default=0)
    notes: str = Field(default="", sa_column=Column(Text, nullable=False))
    metadata_json: dict[str, Any] = Field(default_factory=dict, sa_column=Column(JSON, nullable=False))


class MigrationDatasetStat(TimestampMixin, table=True):
    __tablename__ = "migration_dataset_stats"
    __table_args__ = (UniqueConstraint("migration_run_id", "dataset", name="uq_migration_dataset_stats_run"),)

    id: int | None = Field(default=None, primary_key=True)
    migration_run_id: int = Field(foreign_key="migration_runs.id", index=True)
    dataset: str = Field(max_length=128, index=True)
    source_count: int = Field(default=0)
    target_count: int = Field(default=0)
    inserted_count: int = Field(default=0)
    updated_count: int = Field(default=0)
    failed_count: int = Field(default=0)
    details_json: dict[str, Any] = Field(default_factory=dict, sa_column=Column(JSON, nullable=False))


class MigrationSourceCheckpoint(TimestampMixin, table=True):
    __tablename__ = "migration_source_checkpoints"
    __table_args__ = (UniqueConstraint("dataset", name="uq_migration_source_checkpoints_dataset"),)

    id: int | None = Field(default=None, primary_key=True)
    dataset: str = Field(max_length=128, index=True)
    source_table: str = Field(max_length=128, index=True)
    last_source_id: str | None = Field(default=None, max_length=191)
    last_source_updated_at: datetime | None = Field(default=None, index=True)
    cursor_json: dict[str, Any] = Field(default_factory=dict, sa_column=Column(JSON, nullable=False))


class ReconciliationIssue(TimestampMixin, table=True):
    __tablename__ = "reconciliation_issues"

    id: int | None = Field(default=None, primary_key=True)
    dataset: str = Field(max_length=128, index=True)
    severity: str = Field(default="warning", max_length=32, index=True)
    item_type: str = Field(default="row", max_length=64, index=True)
    source_key: str = Field(max_length=255, index=True)
    field_name: str | None = Field(default=None, max_length=191)
    source_value: str | None = Field(default=None, sa_column=Column(Text, nullable=True))
    target_value: str | None = Field(default=None, sa_column=Column(Text, nullable=True))
    status: str = Field(default="open", max_length=32, index=True)
    notes: str = Field(default="", sa_column=Column(Text, nullable=False))


MIGRATION_MODELS: tuple[type[SQLModel], ...] = (
    MigrationRun,
    MigrationDatasetStat,
    MigrationSourceCheckpoint,
    ReconciliationIssue,
)
