from __future__ import annotations

from datetime import datetime
from typing import Any

from sqlalchemy import JSON, Column, Text, UniqueConstraint
from sqlmodel import Field, SQLModel

from app.models.base import SourceTraceMixin, TimestampMixin, utc_now


class UserAccount(TimestampMixin, SourceTraceMixin, table=True):
    __tablename__ = "user_accounts"
    __table_args__ = (
        UniqueConstraint("source_table", "source_id", name="uq_user_accounts_source"),
        UniqueConstraint("username", name="uq_user_accounts_username"),
        UniqueConstraint("email", name="uq_user_accounts_email"),
    )

    id: int | None = Field(default=None, primary_key=True)
    wordpress_user_id: int | None = Field(default=None, index=True)
    username: str = Field(max_length=191, index=True)
    email: str = Field(max_length=320, index=True)
    nicename: str | None = Field(default=None, max_length=191, index=True)
    display_name: str = Field(default="", max_length=255)
    role_label: str = Field(default="member", max_length=64, index=True)
    password_authority: str = Field(default="wordpress", max_length=64)
    first_relogin_required: bool = Field(default=True, index=True)
    meta_json: dict[str, Any] = Field(default_factory=dict, sa_column=Column(JSON, nullable=False))


class UserProfile(TimestampMixin, SourceTraceMixin, table=True):
    __tablename__ = "user_profiles"
    __table_args__ = (
        UniqueConstraint("source_table", "source_id", name="uq_user_profiles_source"),
        UniqueConstraint("user_id", name="uq_user_profiles_user"),
    )

    id: int | None = Field(default=None, primary_key=True)
    user_id: int = Field(foreign_key="user_accounts.id", index=True)
    avatar_url: str | None = Field(default=None, max_length=1000)
    phone: str | None = Field(default=None, max_length=64)
    bio: str = Field(default="", sa_column=Column(Text, nullable=False))
    meta_json: dict[str, Any] = Field(default_factory=dict, sa_column=Column(JSON, nullable=False))


class UserRole(TimestampMixin, SourceTraceMixin, table=True):
    __tablename__ = "user_roles"
    __table_args__ = (
        UniqueConstraint("source_table", "source_id", name="uq_user_roles_source"),
        UniqueConstraint("user_id", "role_name", name="uq_user_roles_name"),
    )

    id: int | None = Field(default=None, primary_key=True)
    user_id: int = Field(foreign_key="user_accounts.id", index=True)
    role_name: str = Field(max_length=128, index=True)
    capability_json: dict[str, Any] = Field(default_factory=dict, sa_column=Column(JSON, nullable=False))


class SocialIdentity(TimestampMixin, SourceTraceMixin, table=True):
    __tablename__ = "social_identities"
    __table_args__ = (
        UniqueConstraint("source_table", "source_id", name="uq_social_identities_source"),
        UniqueConstraint("provider", "provider_user_id", name="uq_social_identities_provider"),
    )

    id: int | None = Field(default=None, primary_key=True)
    user_id: int | None = Field(default=None, foreign_key="user_accounts.id", index=True)
    provider: str = Field(max_length=64, index=True)
    provider_user_id: str = Field(max_length=255, index=True)
    email: str | None = Field(default=None, max_length=320)
    unionid: str | None = Field(default=None, max_length=255)
    openid: str | None = Field(default=None, max_length=255)
    profile_json: dict[str, Any] = Field(default_factory=dict, sa_column=Column(JSON, nullable=False))


class SessionRefreshToken(TimestampMixin, table=True):
    __tablename__ = "session_refresh_tokens"
    __table_args__ = (UniqueConstraint("token_hash", name="uq_session_refresh_tokens_hash"),)

    id: int | None = Field(default=None, primary_key=True)
    user_id: int = Field(foreign_key="user_accounts.id", index=True)
    token_hash: str = Field(max_length=128, index=True)
    issued_at: datetime = Field(default_factory=utc_now, nullable=False)
    expires_at: datetime = Field(nullable=False, index=True)
    revoked_at: datetime | None = Field(default=None, index=True)
    last_used_at: datetime | None = Field(default=None)
    user_agent: str | None = Field(default=None, max_length=500)
    ip_address: str | None = Field(default=None, max_length=64)


USER_MODELS: tuple[type[SQLModel], ...] = (
    UserAccount,
    UserProfile,
    UserRole,
    SocialIdentity,
    SessionRefreshToken,
)
