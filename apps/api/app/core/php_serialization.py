from __future__ import annotations

from collections.abc import Mapping
from typing import Any

import phpserialize

SERIALIZED_PREFIXES = ("a:", "b:", "d:", "i:", "N;", "O:", "C:", "s:")


def _decode_bytes(value: bytes) -> str:
    return value.decode("utf-8", errors="replace")


def _normalize_php_value(value: Any) -> Any:
    if isinstance(value, bytes):
        return _decode_bytes(value)
    if isinstance(value, Mapping):
        normalized = {_normalize_php_value(key): _normalize_php_value(item) for key, item in value.items()}
        if normalized and all(isinstance(key, int) for key in normalized):
            ordered_keys = sorted(normalized)
            if ordered_keys == list(range(len(ordered_keys))):
                return [normalized[index] for index in ordered_keys]
        return normalized
    if isinstance(value, (list, tuple, set)):
        return [_normalize_php_value(item) for item in value]
    return value


def is_probably_serialized(value: str | bytes | None) -> bool:
    if value is None:
        return False
    text = _decode_bytes(value) if isinstance(value, bytes) else value
    text = text.strip()
    return text.startswith(SERIALIZED_PREFIXES)


def parse_php_serialized(value: str | bytes | None) -> object:
    if value is None:
        return None
    if isinstance(value, bytes):
        raw_bytes = value
        raw_text = _decode_bytes(value)
    else:
        raw_text = value
        raw_bytes = value.encode("utf-8", errors="replace")
    if raw_text == "":
        return ""
    if not is_probably_serialized(raw_text):
        return raw_text
    parsed = phpserialize.loads(raw_bytes, decode_strings=True, object_hook=dict)
    return _normalize_php_value(parsed)


def normalize_meta_value(value: str | bytes | None) -> dict[str, Any]:
    raw = _decode_bytes(value) if isinstance(value, bytes) else value
    if value is None:
        return {"status": "empty", "is_serialized": False, "raw": None, "value": None}
    if raw == "":
        return {"status": "empty", "is_serialized": False, "raw": "", "value": ""}
    serialized = is_probably_serialized(value)
    if not serialized:
        return {"status": "scalar", "is_serialized": False, "raw": raw, "value": raw}
    try:
        parsed = parse_php_serialized(value)
    except Exception:
        return {"status": "malformed", "is_serialized": True, "raw": raw, "value": raw}
    return {"status": "parsed", "is_serialized": True, "raw": raw, "value": parsed}
