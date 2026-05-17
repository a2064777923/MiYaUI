from __future__ import annotations

import secrets
from dataclasses import dataclass
from urllib.parse import urlencode

from app.config import settings

SUPPORTED_SOCIAL_PROVIDERS = ("wechat", "qq", "weibo", "baidu", "google")
INTERNAL_PROVIDER_NAMES = {
    "wechat": "weixin",
    "qq": "qq",
    "weibo": "weibo",
    "baidu": "baidu",
    "google": "google",
}


@dataclass(frozen=True)
class SocialIdentityPayload:
    provider: str
    provider_user_id: str
    email: str | None = None
    unionid: str | None = None
    openid: str | None = None
    profile_json: dict[str, object] | None = None


class UnsupportedSocialProviderError(ValueError):
    pass


def validate_provider(provider: str) -> str:
    normalized = provider.strip().lower()
    if normalized not in SUPPORTED_SOCIAL_PROVIDERS:
        raise UnsupportedSocialProviderError(provider)
    return normalized


def build_provider_state() -> str:
    return secrets.token_urlsafe(18)


def get_authorization_url(provider: str, state: str, redirect_uri: str | None = None) -> str:
    normalized = validate_provider(provider)
    callback_url = redirect_uri or f"{settings.SOCIAL_CALLBACK_BASE_URL.rstrip('/')}/{normalized}"
    query = urlencode(
        {
            "type": normalized,
            "state": state,
            "redirect_uri": callback_url,
        }
    )
    return f"{settings.WORDPRESS_BASE_URL.rstrip('/')}/open?{query}"


def normalize_identity(provider: str, payload: dict[str, object]) -> SocialIdentityPayload:
    normalized = validate_provider(provider)
    provider_user_id = (
        payload.get("provider_user_id")
        or payload.get("uid")
        or payload.get("openid")
        or payload.get("unionid")
    )
    if not provider_user_id:
        raise ValueError("Missing provider user identifier")
    return SocialIdentityPayload(
        provider=normalized,
        provider_user_id=str(provider_user_id),
        email=str(payload["email"]) if payload.get("email") else None,
        unionid=str(payload["unionid"]) if payload.get("unionid") else None,
        openid=str(payload["openid"]) if payload.get("openid") else None,
        profile_json=dict(payload.get("profile") or {}),
    )


def exchange_callback(provider: str, payload: dict[str, object]) -> SocialIdentityPayload:
    return normalize_identity(provider, payload)
