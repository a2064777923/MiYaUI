from __future__ import annotations

from dataclasses import dataclass
from pathlib import Path
from urllib.parse import urljoin

import httpx

from app.services.content import extract_media_urls_from_html


@dataclass
class MediaAuditEntry:
    url: str
    status_code: int | None
    ok: bool
    source: str
    detail: str = ""


def normalize_media_url(url: str, base_url: str) -> str:
    if url.startswith("http://") or url.startswith("https://"):
        return url
    return urljoin(base_url.rstrip("/") + "/", url.lstrip("/"))


def extract_candidate_urls(html_fragments: list[str], base_url: str) -> list[str]:
    urls: list[str] = []
    for fragment in html_fragments:
        urls.extend(normalize_media_url(url, base_url) for url in extract_media_urls_from_html(fragment))
    return list(dict.fromkeys(urls))


def audit_urls(
    urls: list[str],
    *,
    client: httpx.Client,
    sample_size: int | None = None,
) -> list[MediaAuditEntry]:
    audit_urls = urls[:sample_size] if sample_size is not None else urls
    entries: list[MediaAuditEntry] = []
    for url in audit_urls:
        try:
            response = client.head(url, follow_redirects=True)
            if response.status_code >= 400:
                response = client.get(url, follow_redirects=True)
            entries.append(
                MediaAuditEntry(
                    url=url,
                    status_code=response.status_code,
                    ok=response.status_code < 400,
                    source="http",
                )
            )
        except httpx.HTTPError as exc:
            entries.append(MediaAuditEntry(url=url, status_code=None, ok=False, source="http", detail=str(exc)))
    return entries


def write_media_audit(entries: list[MediaAuditEntry], output_path: Path) -> None:
    output_path.parent.mkdir(parents=True, exist_ok=True)
    lines = [
        "# Media Audit",
        "",
        "| URL | Status | OK | Detail |",
        "|-----|--------|----|--------|",
    ]
    for entry in entries:
        status = entry.status_code if entry.status_code is not None else "ERR"
        lines.append(f"| {entry.url} | {status} | {'yes' if entry.ok else 'no'} | {entry.detail} |")
    output_path.write_text("\n".join(lines) + "\n", encoding="utf-8")
