from __future__ import annotations

import hashlib
import secrets
from dataclasses import dataclass
from datetime import timedelta

from fastapi import Response

from app.config import settings
from app.models.base import utc_now


@dataclass(frozen=True)
class SessionTokenBundle:
    raw_token: str
    token_hash: str
    expires_at: object


def hash_session_token(raw_token: str) -> str:
    return hashlib.sha256(raw_token.encode("utf-8")).hexdigest()


def issue_session_token() -> SessionTokenBundle:
    raw_token = secrets.token_urlsafe(32)
    expires_at = utc_now() + timedelta(seconds=settings.AUTH_SESSION_TTL_SECONDS)
    return SessionTokenBundle(
        raw_token=raw_token,
        token_hash=hash_session_token(raw_token),
        expires_at=expires_at,
    )


def set_session_cookie(response: Response, raw_token: str) -> None:
    response.set_cookie(
        key=settings.AUTH_SESSION_COOKIE_NAME,
        value=raw_token,
        max_age=settings.AUTH_SESSION_TTL_SECONDS,
        httponly=True,
        secure=settings.AUTH_SESSION_COOKIE_SECURE,
        samesite=settings.AUTH_SESSION_COOKIE_SAMESITE,
        domain=settings.AUTH_SESSION_COOKIE_DOMAIN,
        path="/",
    )


def clear_session_cookie(response: Response) -> None:
    response.delete_cookie(
        key=settings.AUTH_SESSION_COOKIE_NAME,
        httponly=True,
        secure=settings.AUTH_SESSION_COOKIE_SECURE,
        samesite=settings.AUTH_SESSION_COOKIE_SAMESITE,
        domain=settings.AUTH_SESSION_COOKIE_DOMAIN,
        path="/",
    )
