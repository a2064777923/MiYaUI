from __future__ import annotations

import argparse
import asyncio
import json
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
API_ROOT = ROOT / "apps" / "api"
if str(API_ROOT) not in sys.path:
    sys.path.insert(0, str(API_ROOT))

from app.core.database import async_session
from app.migration import DATASETS
from app.migration.extractors import WordPressExtractor
from app.migration.mysql import WordPressMySQLClient
from app.migration.sync import sync_dataset


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Run MiyaUI Phase 2 incremental sync")
    parser.add_argument("--dataset", choices=DATASETS, default="content")
    parser.add_argument("--all", action="store_true", dest="run_all")
    parser.add_argument("--since-checkpoint", default="latest")
    parser.add_argument("--limit", type=int, default=None)
    return parser.parse_args()


async def main() -> int:
    args = parse_args()
    datasets = list(DATASETS) if args.run_all else [args.dataset]
    with WordPressMySQLClient.from_settings() as client:
        extractor = WordPressExtractor(client)
        async with async_session() as session:
            results = []
            for dataset in datasets:
                sync_result = await sync_dataset(session, extractor, dataset, limit=args.limit)
                results.append(
                    {
                        "dataset": dataset,
                        "full_refresh": sync_result.full_refresh,
                        "last_source_id": sync_result.last_source_id,
                        "inserted_count": sync_result.load_result.inserted_count,
                        "updated_count": sync_result.load_result.updated_count,
                    }
                )
            await session.commit()
    print(json.dumps(results, ensure_ascii=False, indent=2))
    return 0


if __name__ == "__main__":
    raise SystemExit(asyncio.run(main()))
