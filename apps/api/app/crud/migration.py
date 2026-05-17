from __future__ import annotations

from datetime import datetime
from typing import Any

from sqlalchemy import select
from sqlalchemy.ext.asyncio import AsyncSession

from app.models.migration import MigrationDatasetStat, MigrationRun, MigrationSourceCheckpoint


async def create_migration_run(
    session: AsyncSession,
    dataset: str,
    mode: str,
    dry_run: bool,
    notes: str = "",
) -> MigrationRun:
    run = MigrationRun(dataset=dataset, mode=mode, dry_run=dry_run, status="running", notes=notes)
    session.add(run)
    await session.flush()
    return run


async def finalize_migration_run(
    session: AsyncSession,
    run: MigrationRun,
    *,
    status: str,
    records_read: int,
    records_written: int,
    records_failed: int = 0,
    notes: str = "",
    metadata_json: dict[str, Any] | None = None,
) -> MigrationRun:
    run.status = status
    run.completed_at = datetime.utcnow()
    run.records_read = records_read
    run.records_written = records_written
    run.records_failed = records_failed
    if notes:
        run.notes = notes
    if metadata_json:
        run.metadata_json = metadata_json
    await session.flush()
    return run


async def upsert_dataset_stat(
    session: AsyncSession,
    run: MigrationRun,
    dataset: str,
    *,
    source_count: int,
    target_count: int,
    inserted_count: int,
    updated_count: int,
    failed_count: int,
    details_json: dict[str, Any] | None = None,
) -> MigrationDatasetStat:
    result = await session.execute(
        select(MigrationDatasetStat).where(
            MigrationDatasetStat.migration_run_id == run.id,
            MigrationDatasetStat.dataset == dataset,
        )
    )
    stat = result.scalar_one_or_none()
    if stat is None:
        stat = MigrationDatasetStat(migration_run_id=run.id, dataset=dataset)
        session.add(stat)
    stat.source_count = source_count
    stat.target_count = target_count
    stat.inserted_count = inserted_count
    stat.updated_count = updated_count
    stat.failed_count = failed_count
    stat.details_json = details_json or {}
    await session.flush()
    return stat


async def get_checkpoint(session: AsyncSession, dataset: str) -> MigrationSourceCheckpoint | None:
    result = await session.execute(
        select(MigrationSourceCheckpoint).where(MigrationSourceCheckpoint.dataset == dataset)
    )
    return result.scalar_one_or_none()


async def upsert_checkpoint(
    session: AsyncSession,
    dataset: str,
    *,
    source_table: str,
    last_source_id: str | None,
    last_source_updated_at: datetime | None,
    cursor_json: dict[str, Any] | None = None,
) -> MigrationSourceCheckpoint:
    checkpoint = await get_checkpoint(session, dataset)
    if checkpoint is None:
        checkpoint = MigrationSourceCheckpoint(dataset=dataset, source_table=source_table)
        session.add(checkpoint)
    checkpoint.source_table = source_table
    checkpoint.last_source_id = last_source_id
    checkpoint.last_source_updated_at = last_source_updated_at
    checkpoint.cursor_json = cursor_json or {}
    await session.flush()
    return checkpoint
