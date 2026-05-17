from __future__ import annotations

import re
from html import unescape
from typing import Any

MEDIA_URL_RE = re.compile(r"""(?:src|href)=["'](?P<url>[^"']+)["']""", re.IGNORECASE)
TAG_RE = re.compile(r"<[^>]+>")


def build_original_url_path(post_id: int | str) -> str:
    return f"/{post_id}.html"


def build_content_excerpt(body: str, excerpt: str, max_length: int = 180) -> str:
    if excerpt.strip():
        return excerpt.strip()[:max_length]
    text = TAG_RE.sub(" ", unescape(body or ""))
    text = " ".join(text.split())
    return text[:max_length]


def build_content_search_text(title: str, excerpt: str, body: str) -> str:
    text = " ".join(part for part in (title, excerpt, TAG_RE.sub(" ", body or "")) if part)
    return " ".join(unescape(text).split())


def build_content_seo(meta_json: dict[str, Any], title: str, excerpt: str, original_url_path: str) -> dict[str, Any]:
    seo_title = meta_json.get("_yoast_wpseo_title") or meta_json.get("_aioseo_title") or title
    seo_description = (
        meta_json.get("_yoast_wpseo_metadesc")
        or meta_json.get("_aioseo_description")
        or build_content_excerpt("", excerpt)
    )
    return {
        "title": seo_title,
        "description": seo_description,
        "canonical_path": original_url_path,
    }


def extract_media_urls_from_html(html: str) -> list[str]:
    if not html:
        return []
    return list(dict.fromkeys(match.group("url") for match in MEDIA_URL_RE.finditer(html)))
