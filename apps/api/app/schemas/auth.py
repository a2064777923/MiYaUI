from __future__ import annotations

from typing import Any

from pydantic import BaseModel, Field


class LoginRequest(BaseModel):
    username_or_email: str
    password: str


class RegisterRequest(BaseModel):
    username: str
    email: str
    display_name: str | None = None


class ResetPasswordRequest(BaseModel):
    email_or_username: str


class AuthActionResponse(BaseModel):
    status: str
    message: str
    redirect_url: str | None = None


class SocialAuthStartResponse(BaseModel):
    provider: str
    state: str
    authorization_url: str


class SocialCallbackRequest(BaseModel):
    code: str | None = None
    state: str | None = None
    provider_user_id: str | None = None
    email: str | None = None
    unionid: str | None = None
    openid: str | None = None
    profile: dict[str, Any] = Field(default_factory=dict)


class SocialIdentityResult(BaseModel):
    provider: str
    provider_user_id: str
    email: str | None = None
    unionid: str | None = None
    openid: str | None = None
    profile_json: dict[str, Any] = Field(default_factory=dict)


class SocialCallbackResponse(BaseModel):
    status: str
    identity: SocialIdentityResult
    linked_user_id: int | None = None
