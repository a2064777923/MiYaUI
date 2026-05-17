from __future__ import annotations

import argparse
import asyncio
import json
import sys
from pathlib import Path
from typing import Any

ROOT = Path(__file__).resolve().parents[2]
API_ROOT = ROOT / "apps" / "api"
if str(API_ROOT) not in sys.path:
    sys.path.insert(0, str(API_ROOT))

from app.core.database import async_session
from app.crud.migration import create_migration_run, finalize_migration_run, upsert_dataset_stat
from app.migration import DATASETS
from app.migration.extractors import WordPressExtractor
from app.migration.loaders import load_dataset
from app.migration.mysql import WordPressMySQLClient


async def run_dataset(dataset: str, dry_run: bool, limit: int | None) -> dict[str, Any]:
    with WordPressMySQLClient.from_settings() as client:
        extractor = WordPressExtractor(client)
        payloads = extractor.extract_dataset(dataset, limit=limit)
        source_count = extractor.source_count_for_dataset(dataset)

    async with async_session() as session:
        run = await create_migration_run(session, dataset=dataset, mode="backfill", dry_run=dry_run)
        if dry_run:
            await finalize_migration_run(
                session,
                run,
                status="dry-run",
                records_read=len(payloads),
                records_written=0,
                metadata_json={"limit": limit},
            )
            await upsert_dataset_stat(
                session,
                run,
                dataset,
                source_count=source_count,
                target_count=0,
                inserted_count=0,
                updated_count=0,
                failed_count=0,
                details_json={"dry_run": True},
            )
            await session.commit()
            return {"dataset": dataset, "mode": "dry-run", "source_count": source_count, "payload_count": len(payloads)}

        load_result = await load_dataset(session, dataset, payloads)
        await finalize_migration_run(
            session,
            run,
            status="completed",
            records_read=load_result.source_count,
            records_written=load_result.records_written,
            records_failed=load_result.failed_count,
            metadata_json={"limit": limit},
        )
        await upsert_dataset_stat(
            session,
            run,
            dataset,
            source_count=source_count,
            target_count=load_result.records_written,
            inserted_count=load_result.inserted_count,
            updated_count=load_result.updated_count,
            failed_count=load_result.failed_count,
            details_json={"dry_run": False},
        )
        await session.commit()
        return {
            "dataset": dataset,
            "mode": "backfill",
            "source_count": source_count,
            "payload_count": load_result.source_count,
            "inserted_count": load_result.inserted_count,
            "updated_count": load_result.updated_count,
        }


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Run MiyaUI Phase 2 WordPress backfill")
    parser.add_argument("--dataset", choices=DATASETS, default="content")
    parser.add_argument("--all", action="store_true", dest="run_all")
    parser.add_argument("--dry-run", action="store_true")
    parser.add_argument("--limit", type=int, default=None)
    parser.add_argument("--since-id", type=int, default=None)
    return parser.parse_args()


async def main() -> int:
    args = parse_args()
    datasets = list(DATASETS) if args.run_all else [args.dataset]
    results = []
    for dataset in datasets:
        results.append(await run_dataset(dataset, dry_run=args.dry_run, limit=args.limit))
    print(json.dumps(results, ensure_ascii=False, indent=2))
    return 0


if __name__ == "__main__":
    raise SystemExit(asyncio.run(main()))
