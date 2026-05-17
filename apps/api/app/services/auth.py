from __future__ import annotations

from dataclasses import dataclass
from datetime import UTC, datetime

from fastapi import Request
from sqlalchemy import select
from sqlalchemy.ext.asyncio import AsyncSession

from app.config import settings
from app.core.security import SessionTokenBundle, hash_session_token, issue_session_token
from app.crud.users import (
    get_user_by_wordpress_user_id,
    list_user_role_names,
    replace_user_roles,
    upsert_user_account,
)
from app.migration.extractors import map_wordpress_roles
from app.models.base import utc_now
from app.models.users import SessionRefreshToken, UserAccount
from app.schemas.users import SessionRead, UserRead
from app.services.wordpress_auth import VerifiedWordPressUser


@dataclass
class AuthenticatedSession:
    user: UserAccount
    role_names: list[str]
    refresh_token: SessionRefreshToken
    token_bundle: SessionTokenBundle | None = None


def _user_source_table() -> str:
    return f"{settings.WORDPRESS_TABLE_PREFIX}users"


def _user_role_payloads(
    verified_user: VerifiedWordPressUser,
    role_names: list[str],
) -> list[dict[str, object]]:
    capabilities = verified_user.capabilities or {role_name: True for role_name in role_names}
    names = role_names or list(capabilities.keys())
    return [
        {
            "role_name": role_name,
            "capability_json": capabilities,
            "source_table": f"{settings.WORDPRESS_TABLE_PREFIX}usermeta",
            "source_id": f"{verified_user.wordpress_user_id}:{role_name}",
        }
        for role_name in names
    ]


async def sync_wordpress_user(
    session: AsyncSession,
    verified_user: VerifiedWordPressUser,
) -> tuple[UserAccount, list[str]]:
    capabilities = verified_user.capabilities or {role_name: True for role_name in verified_user.roles}
    role_names, role_label = map_wordpress_roles(capabilities)
    if not role_names:
        role_names = verified_user.roles
    user = await get_user_by_wordpress_user_id(session, verified_user.wordpress_user_id)
    payload = {
        "source_system": "wordpress",
        "source_table": _user_source_table(),
        "source_id": str(verified_user.wordpress_user_id),
        "wordpress_user_id": verified_user.wordpress_user_id,
        "username": verified_user.login,
        "email": verified_user.email,
        "nicename": verified_user.nicename or verified_user.login,
        "display_name": verified_user.display_name,
        "role_label": role_label,
        "password_authority": "wordpress",
        "first_relogin_required": False,
        "meta_json": {
            "wordpress_roles": verified_user.roles,
            "wordpress_capabilities": capabilities,
        },
    }
    if user is not None:
        payload["source_table"] = user.source_table
        payload["source_id"] = user.source_id
    user, _ = await upsert_user_account(session, payload)
    await replace_user_roles(session, user, _user_role_payloads(verified_user, role_names))
    return user, role_names


def _request_ip(request: Request) -> str | None:
    forwarded_for = request.headers.get("x-forwarded-for")
    if forwarded_for:
        return forwarded_for.split(",")[0].strip()
    if request.client:
        return request.client.host
    return None


def _as_utc_datetime(value: datetime) -> datetime:
    if value.tzinfo is None:
        return value
    return value.astimezone(UTC).replace(tzinfo=None)


async def create_authenticated_session(
    session: AsyncSession,
    *,
    user: UserAccount,
    request: Request,
) -> AuthenticatedSession:
    token_bundle = issue_session_token()
    refresh_token = SessionRefreshToken(
        user_id=user.id,
        token_hash=token_bundle.token_hash,
        expires_at=token_bundle.expires_at,
        user_agent=request.headers.get("user-agent"),
        ip_address=_request_ip(request),
    )
    session.add(refresh_token)
    await session.flush()
    role_names = await list_user_role_names(session, user.id)
    return AuthenticatedSession(
        user=user,
        role_names=role_names,
        refresh_token=refresh_token,
        token_bundle=token_bundle,
    )


async def get_authenticated_session(
    session: AsyncSession,
    raw_token: str | None,
) -> AuthenticatedSession | None:
    if not raw_token:
        return None
    result = await session.execute(
        select(SessionRefreshToken).where(
            SessionRefreshToken.token_hash == hash_session_token(raw_token),
        )
    )
    refresh_token = result.scalar_one_or_none()
    if refresh_token is None:
        return None
    if refresh_token.revoked_at is not None or _as_utc_datetime(refresh_token.expires_at) <= utc_now():
        return None
    user_result = await session.execute(select(UserAccount).where(UserAccount.id == refresh_token.user_id))
    user = user_result.scalar_one_or_none()
    if user is None:
        return None
    refresh_token.last_used_at = utc_now()
    role_names = await list_user_role_names(session, user.id)
    return AuthenticatedSession(
        user=user,
        role_names=role_names,
        refresh_token=refresh_token,
    )


async def revoke_authenticated_session(session: AsyncSession, raw_token: str | None) -> bool:
    authenticated_session = await get_authenticated_session(session, raw_token)
    if authenticated_session is None:
        return False
    authenticated_session.refresh_token.revoked_at = utc_now()
    await session.flush()
    return True


def build_session_read(authenticated_session: AuthenticatedSession) -> SessionRead:
    return SessionRead(
        user=UserRead.model_validate(authenticated_session.user, from_attributes=True),
        expires_at=authenticated_session.refresh_token.expires_at,
        role_names=authenticated_session.role_names,
    )
