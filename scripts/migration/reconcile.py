from __future__ import annotations

import argparse
import asyncio
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
from app.migration.reconcile import ReconciliationReport, reconcile_dataset


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Generate reconciliation report for MiyaUI migration")
    parser.add_argument("--dataset", choices=DATASETS, default="content")
    parser.add_argument("--sample-size", type=int, default=10)
    parser.add_argument("--out", type=Path, required=True)
    parser.add_argument("--fail-on-mismatch", action="store_true")
    return parser.parse_args()


async def main() -> int:
    args = parse_args()
    with WordPressMySQLClient.from_settings() as client:
        extractor = WordPressExtractor(client)
        source_payloads = extractor.extract_dataset(args.dataset)
        source_count = extractor.source_count_for_dataset(args.dataset)
    async with async_session() as session:
        summary = await reconcile_dataset(
            session,
            dataset=args.dataset,
            source_payloads=source_payloads,
            source_count=source_count,
            sample_size=args.sample_size,
        )
    report = ReconciliationReport([summary])
    report.write(args.out)
    if args.fail_on_mismatch and report.has_mismatch:
        return 1
    return 0


if __name__ == "__main__":
    raise SystemExit(asyncio.run(main()))
