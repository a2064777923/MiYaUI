import pytest

from app.services.social_oauth import (
    SUPPORTED_SOCIAL_PROVIDERS,
    UnsupportedSocialProviderError,
    exchange_callback,
    get_authorization_url,
)


def test_supported_social_provider_registry_is_complete():
    assert tuple(SUPPORTED_SOCIAL_PROVIDERS) == ("wechat", "qq", "weibo", "baidu", "google")


def test_get_authorization_url_accepts_known_provider():
    url = get_authorization_url("wechat", "abc123")
    assert "type=wechat" in url
    assert "state=abc123" in url


def test_unsupported_provider_rejected():
    with pytest.raises(UnsupportedSocialProviderError):
        get_authorization_url("github", "abc123")


def test_exchange_callback_normalizes_identity_payload():
    identity = exchange_callback(
        "google",
        {
            "provider_user_id": "google-123",
            "email": "alice@example.com",
            "profile": {"name": "Alice"},
        },
    )
    assert identity.provider == "google"
    assert identity.provider_user_id == "google-123"
    assert identity.email == "alice@example.com"
