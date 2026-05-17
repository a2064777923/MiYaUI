from __future__ import annotations

from dataclasses import dataclass

from sqlalchemy.ext.asyncio import AsyncSession

from app.crud.migration import get_checkpoint, upsert_checkpoint
from app.migration.extractors import DATASET_TABLES, WordPressExtractor
from app.migration.loaders import LoadResult, load_dataset

FULL_REFRESH_DATASETS = {"community"}


@dataclass
class SyncResult:
    dataset: str
    load_result: LoadResult
    last_source_id: str | None
    full_refresh: bool


async def sync_dataset(
    session: AsyncSession,
    extractor: WordPressExtractor,
    dataset: str,
    *,
    limit: int | None = None,
) -> SyncResult:
    checkpoint = await get_checkpoint(session, dataset)
    if dataset in FULL_REFRESH_DATASETS:
        since_id = None
    elif checkpoint and checkpoint.last_source_id:
        since_id = int(checkpoint.last_source_id)
    else:
        since_id = None
    payloads = extractor.extract_dataset(dataset, since_id=since_id, limit=limit)
    load_result = await load_dataset(session, dataset, payloads)
    last_source_id = payloads[-1]["source_id"] if payloads else checkpoint.last_source_id if checkpoint else None
    if payloads:
        last_source_updated_at = payloads[-1].get("source_updated_at")
    elif checkpoint:
        last_source_updated_at = checkpoint.last_source_updated_at
    else:
        last_source_updated_at = None
    await upsert_checkpoint(
        session,
        dataset,
        source_table=f"{extractor.prefix}{DATASET_TABLES[dataset]}",
        last_source_id=last_source_id,
        last_source_updated_at=last_source_updated_at,
        cursor_json={"mode": "full-refresh" if dataset in FULL_REFRESH_DATASETS else "incremental"},
    )
    return SyncResult(
        dataset=dataset,
        load_result=load_result,
        last_source_id=last_source_id,
        full_refresh=dataset in FULL_REFRESH_DATASETS,
    )
