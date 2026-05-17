from __future__ import annotations

from datetime import datetime
from typing import Any

from pydantic import BaseModel, Field


class UserRead(BaseModel):
    id: int
    username: str
    email: str
    nicename: str | None = None
    display_name: str = ""
    role_label: str = "member"
    first_relogin_required: bool = True
    meta_json: dict[str, Any] = Field(default_factory=dict)


class SessionRead(BaseModel):
    user: UserRead
    expires_at: datetime
    role_names: list[str] = Field(default_factory=list)


class SocialIdentityRead(BaseModel):
    provider: str
    provider_user_id: str
    email: str | None = None
    unionid: str | None = None
    openid: str | None = None
