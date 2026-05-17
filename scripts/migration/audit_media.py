from __future__ import annotations

import argparse
import csv
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
API_ROOT = ROOT / "apps" / "api"
if str(API_ROOT) not in sys.path:
    sys.path.insert(0, str(API_ROOT))

import httpx

from app.migration.media_audit import audit_urls, write_media_audit


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Audit media URLs from URL inventory")
    parser.add_argument("--input", type=Path, required=True)
    parser.add_argument("--base-url", default="http://localhost:8082")
    parser.add_argument("--sample-size", type=int, default=20)
    parser.add_argument("--out", type=Path, required=True)
    parser.add_argument("--fail-on-broken", action="store_true")
    return parser.parse_args()


def read_urls(csv_path: Path, sample_size: int) -> list[str]:
    urls: list[str] = []
    with csv_path.open("r", encoding="utf-8") as handle:
        reader = csv.DictReader(handle)
        for row in reader:
            url_path = row.get("url_path")
            if url_path:
                urls.append(url_path)
            if len(urls) >= sample_size:
                break
    return urls


def main() -> int:
    args = parse_args()
    urls = [f"{args.base_url.rstrip('/')}{path}" for path in read_urls(args.input, args.sample_size)]
    with httpx.Client(timeout=15, follow_redirects=True) as client:
        entries = audit_urls(urls, client=client, sample_size=args.sample_size)
    write_media_audit(entries, args.out)
    if args.fail_on_broken and any(not entry.ok for entry in entries):
        return 1
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
