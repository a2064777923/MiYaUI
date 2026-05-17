from pathlib import Path

import httpx

from app.migration.media_audit import (
    MediaAuditEntry,
    audit_urls,
    extract_candidate_urls,
    write_media_audit,
)


class FakeClient:
    def __init__(self, responses: dict[str, int]):
        self.responses = responses

    def head(self, url: str, follow_redirects: bool = True):
        if url not in self.responses:
            raise httpx.ConnectError("missing", request=httpx.Request("HEAD", url))
        return httpx.Response(self.responses[url], request=httpx.Request("HEAD", url))

    def get(self, url: str, follow_redirects: bool = True):
        return httpx.Response(self.responses[url], request=httpx.Request("GET", url))


def test_extract_candidate_urls_normalizes_relative_sources():
    urls = extract_candidate_urls(['<img src="/uploads/a.jpg"><a href="https://cdn.example/b.pdf">'], "https://www.miyaui.com")
    assert urls == ["https://www.miyaui.com/uploads/a.jpg", "https://cdn.example/b.pdf"]


def test_audit_urls_reports_failures():
    client = FakeClient({"https://www.miyaui.com/uploads/a.jpg": 200, "https://cdn.example/b.pdf": 404})
    entries = audit_urls(
        ["https://www.miyaui.com/uploads/a.jpg", "https://cdn.example/b.pdf"],
        client=client,
    )
    assert entries[0].ok is True
    assert entries[1].ok is False


def test_write_media_audit_creates_markdown(tmp_path: Path):
    output = tmp_path / "audit.md"
    write_media_audit(
        [MediaAuditEntry(url="https://example.com/a.jpg", status_code=200, ok=True, source="http")],
        output,
    )
    assert output.exists()
    assert "https://example.com/a.jpg" in output.read_text(encoding="utf-8")
