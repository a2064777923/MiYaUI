from __future__ import annotations

from typing import Any

from sqlalchemy import delete, or_, select
from sqlalchemy.ext.asyncio import AsyncSession

from app.crud.common import upsert_by_source
from app.models.users import SocialIdentity, UserAccount, UserProfile, UserRole


async def upsert_user_account(session: AsyncSession, payload: dict[str, Any]) -> tuple[UserAccount, bool]:
    instance, created = await upsert_by_source(session, UserAccount, payload)
    return instance, created  # type: ignore[return-value]


async def replace_user_profile(session: AsyncSession, user: UserAccount, payload: dict[str, Any]) -> None:
    if not payload:
        return
    payload = dict(payload)
    payload.setdefault("user_id", user.id)
    payload.setdefault("source_system", "wordpress")
    payload.setdefault("source_table", "wp_usermeta")
    payload.setdefault("source_id", str(user.wordpress_user_id or user.id))
    await upsert_by_source(session, UserProfile, payload)


async def replace_user_roles(session: AsyncSession, user: UserAccount, roles: list[dict[str, Any]]) -> None:
    await session.execute(delete(UserRole).where(UserRole.user_id == user.id))
    for role in roles:
        session.add(
            UserRole(
                user_id=user.id,
                role_name=role["role_name"],
                capability_json=role.get("capability_json", {}),
                source_system="wordpress",
                source_table=role.get("source_table", "wp_usermeta"),
                source_id=role["source_id"],
                source_updated_at=role.get("source_updated_at"),
            )
        )
    await session.flush()


async def replace_social_identities(
    session: AsyncSession,
    user: UserAccount,
    identities: list[dict[str, Any]],
) -> None:
    if not identities:
        return
    providers = [identity["provider"] for identity in identities]
    await session.execute(
        delete(SocialIdentity).where(SocialIdentity.user_id == user.id, SocialIdentity.provider.in_(providers))
    )
    for identity in identities:
        session.add(
            SocialIdentity(
                user_id=user.id,
                provider=identity["provider"],
                provider_user_id=identity["provider_user_id"],
                email=identity.get("email"),
                unionid=identity.get("unionid"),
                openid=identity.get("openid"),
                profile_json=identity.get("profile_json", {}),
                source_system="wordpress",
                source_table=identity.get("source_table", "wp_usermeta"),
                source_id=identity["source_id"],
                source_updated_at=identity.get("source_updated_at"),
            )
        )
    await session.flush()


async def get_user_by_source_id(session: AsyncSession, source_id: str) -> UserAccount | None:
    result = await session.execute(select(UserAccount).where(UserAccount.source_id == source_id))
    return result.scalar_one_or_none()


async def get_user_by_wordpress_user_id(session: AsyncSession, wordpress_user_id: int) -> UserAccount | None:
    result = await session.execute(
        select(UserAccount).where(UserAccount.wordpress_user_id == wordpress_user_id)
    )
    return result.scalar_one_or_none()


async def get_user_by_username_or_email(session: AsyncSession, login: str) -> UserAccount | None:
    result = await session.execute(
        select(UserAccount).where(or_(UserAccount.username == login, UserAccount.email == login))
    )
    return result.scalar_one_or_none()


async def get_user_by_nicename(session: AsyncSession, nicename: str) -> UserAccount | None:
    result = await session.execute(select(UserAccount).where(UserAccount.nicename == nicename))
    return result.scalar_one_or_none()


async def list_user_role_names(session: AsyncSession, user_id: int) -> list[str]:
    result = await session.execute(select(UserRole.role_name).where(UserRole.user_id == user_id))
    return list(result.scalars().all())
