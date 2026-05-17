from __future__ import annotations

from dataclasses import dataclass, field

import httpx

from app.config import settings
from app.core.wordpress import build_wordpress_auth_headers, get_wordpress_bridge_config


@dataclass(frozen=True)
class VerifiedWordPressUser:
    wordpress_user_id: int
    login: str
    email: str
    display_name: str
    nicename: str | None = None
    roles: list[str] = field(default_factory=list)
    capabilities: dict[str, bool] = field(default_factory=dict)


class WordPressAuthError(Exception):
    pass


class WordPressBridgeTimeoutError(WordPressAuthError):
    pass


class WordPressBridgeUnavailableError(WordPressAuthError):
    pass


class WordPressInvalidCredentialsError(WordPressAuthError):
    pass


class WordPressUserNotFoundError(WordPressAuthError):
    pass


class WordPressAuthClient:
    def __init__(
        self,
        *,
        client: httpx.AsyncClient | None = None,
    ) -> None:
        self._client = client

    async def verify_password(
        self,
        *,
        username_or_email: str,
        password: str,
    ) -> VerifiedWordPressUser:
        bridge_config = get_wordpress_bridge_config()
        owned_client = self._client is None
        client = self._client or httpx.AsyncClient(timeout=settings.WORDPRESS_AUTH_BRIDGE_TIMEOUT_SECONDS)
        try:
            response = await client.post(
                bridge_config.verify_url,
                headers=build_wordpress_auth_headers(),
                json={
                    "username_or_email": username_or_email,
                    "password": password,
                },
            )
        except httpx.TimeoutException as exc:
            raise WordPressBridgeTimeoutError("WordPress auth bridge timed out") from exc
        except httpx.HTTPError as exc:
            raise WordPressBridgeUnavailableError("WordPress auth bridge is unavailable") from exc
        finally:
            if owned_client:
                await client.aclose()

        if response.status_code == 401:
            payload = response.json()
            code = payload.get("code")
            if code == "user_not_found":
                raise WordPressUserNotFoundError("WordPress user was not found")
            raise WordPressInvalidCredentialsError("Invalid WordPress credentials")

        if response.status_code >= 500:
            raise WordPressBridgeUnavailableError("WordPress auth bridge failed")

        if response.status_code != 200:
            raise WordPressAuthError(f"Unexpected WordPress auth bridge status: {response.status_code}")

        payload = response.json()
        user_payload = payload.get("user") or {}
        return VerifiedWordPressUser(
            wordpress_user_id=int(user_payload["wordpress_user_id"]),
            login=user_payload["login"],
            email=user_payload["email"],
            display_name=user_payload.get("display_name") or user_payload["login"],
            nicename=user_payload.get("nicename"),
            roles=list(user_payload.get("roles") or []),
            capabilities=dict(user_payload.get("capabilities") or {}),
        )
