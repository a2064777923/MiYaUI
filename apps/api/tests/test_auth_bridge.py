import httpx
import pytest

from app.services.wordpress_auth import (
    WordPressAuthClient,
    WordPressBridgeTimeoutError,
    WordPressBridgeUnavailableError,
    WordPressInvalidCredentialsError,
    WordPressUserNotFoundError,
)


def build_mock_client(handler):
    transport = httpx.MockTransport(handler)
    return httpx.AsyncClient(transport=transport)


@pytest.mark.asyncio
async def test_verify_password_success():
    async with build_mock_client(
        lambda request: httpx.Response(
            200,
            json={
                "authenticated": True,
                "user": {
                    "wordpress_user_id": 8,
                    "login": "alice",
                    "email": "alice@example.com",
                    "display_name": "Alice",
                    "nicename": "alice",
                    "roles": ["administrator"],
                    "capabilities": {"administrator": True},
                },
            },
        )
    ) as client:
        bridge = WordPressAuthClient(client=client)
        user = await bridge.verify_password(username_or_email="alice", password="secret")
    assert user.wordpress_user_id == 8
    assert user.roles == ["administrator"]


@pytest.mark.asyncio
async def test_verify_password_invalid_credentials():
    async with build_mock_client(
        lambda request: httpx.Response(401, json={"code": "invalid_credentials"})
    ) as client:
        bridge = WordPressAuthClient(client=client)
        with pytest.raises(WordPressInvalidCredentialsError):
            await bridge.verify_password(username_or_email="alice", password="wrong")


@pytest.mark.asyncio
async def test_verify_password_missing_user():
    async with build_mock_client(
        lambda request: httpx.Response(401, json={"code": "user_not_found"})
    ) as client:
        bridge = WordPressAuthClient(client=client)
        with pytest.raises(WordPressUserNotFoundError):
            await bridge.verify_password(username_or_email="missing", password="secret")


@pytest.mark.asyncio
async def test_verify_password_bridge_unavailable():
    async with build_mock_client(lambda request: httpx.Response(503, json={"detail": "down"})) as client:
        bridge = WordPressAuthClient(client=client)
        with pytest.raises(WordPressBridgeUnavailableError):
            await bridge.verify_password(username_or_email="alice", password="secret")


@pytest.mark.asyncio
async def test_verify_password_timeout():
    def handler(request):
        raise httpx.ReadTimeout("timed out")

    async with build_mock_client(handler) as client:
        bridge = WordPressAuthClient(client=client)
        with pytest.raises(WordPressBridgeTimeoutError):
            await bridge.verify_password(username_or_email="alice", password="secret")
