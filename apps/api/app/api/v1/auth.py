from __future__ import annotations

from fastapi import APIRouter, Depends, HTTPException, Request, Response, status
from sqlalchemy.ext.asyncio import AsyncSession

from app.config import settings
from app.core.database import get_db
from app.core.security import clear_session_cookie, set_session_cookie
from app.schemas.auth import (
    AuthActionResponse,
    LoginRequest,
    RegisterRequest,
    ResetPasswordRequest,
    SocialAuthStartResponse,
    SocialCallbackRequest,
    SocialCallbackResponse,
    SocialIdentityResult,
)
from app.schemas.users import SessionRead
from app.services.auth import (
    build_session_read,
    create_authenticated_session,
    get_authenticated_session,
    revoke_authenticated_session,
    sync_wordpress_user,
)
from app.services.social_oauth import (
    UnsupportedSocialProviderError,
    build_provider_state,
    exchange_callback,
    get_authorization_url,
)
from app.services.wordpress_auth import (
    WordPressAuthClient,
    WordPressBridgeTimeoutError,
    WordPressBridgeUnavailableError,
    WordPressInvalidCredentialsError,
    WordPressUserNotFoundError,
)

router = APIRouter(prefix="/auth", tags=["auth"])


def _wordpress_register_url() -> str:
    return f"{settings.WORDPRESS_BASE_URL.rstrip('/')}/wp-login.php?action=register"


def _wordpress_reset_url() -> str:
    return f"{settings.WORDPRESS_BASE_URL.rstrip('/')}/wp-login.php?action=lostpassword"


@router.post("/login", response_model=SessionRead)
async def login(
    payload: LoginRequest,
    request: Request,
    response: Response,
    db: AsyncSession = Depends(get_db),
) -> SessionRead:
    auth_client = WordPressAuthClient()
    try:
        verified_user = await auth_client.verify_password(
            username_or_email=payload.username_or_email,
            password=payload.password,
        )
    except (WordPressInvalidCredentialsError, WordPressUserNotFoundError) as exc:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid username/email or password",
        ) from exc
    except WordPressBridgeTimeoutError as exc:
        raise HTTPException(
            status_code=status.HTTP_504_GATEWAY_TIMEOUT,
            detail="WordPress authentication bridge timed out",
        ) from exc
    except WordPressBridgeUnavailableError as exc:
        raise HTTPException(
            status_code=status.HTTP_503_SERVICE_UNAVAILABLE,
            detail="WordPress authentication bridge unavailable",
        ) from exc

    user, _ = await sync_wordpress_user(db, verified_user)
    authenticated_session = await create_authenticated_session(db, user=user, request=request)
    set_session_cookie(response, authenticated_session.token_bundle.raw_token)
    return build_session_read(authenticated_session)


@router.get("/session", response_model=SessionRead)
async def get_session(
    request: Request,
    db: AsyncSession = Depends(get_db),
) -> SessionRead:
    raw_token = request.cookies.get(settings.AUTH_SESSION_COOKIE_NAME)
    authenticated_session = await get_authenticated_session(db, raw_token)
    if authenticated_session is None:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Not authenticated")
    return build_session_read(authenticated_session)


@router.post("/logout", response_model=AuthActionResponse)
async def logout(
    request: Request,
    response: Response,
    db: AsyncSession = Depends(get_db),
) -> AuthActionResponse:
    raw_token = request.cookies.get(settings.AUTH_SESSION_COOKIE_NAME)
    await revoke_authenticated_session(db, raw_token)
    clear_session_cookie(response)
    return AuthActionResponse(status="ok", message="Session cleared")


@router.post("/register", response_model=AuthActionResponse, status_code=status.HTTP_202_ACCEPTED)
async def register(payload: RegisterRequest) -> AuthActionResponse:
    return AuthActionResponse(
        status="pending_wordpress",
        message=f"Registration for {payload.username} must complete through WordPress during Phase 2.",
        redirect_url=_wordpress_register_url(),
    )


@router.post("/reset-password", response_model=AuthActionResponse, status_code=status.HTTP_202_ACCEPTED)
async def reset_password(payload: ResetPasswordRequest) -> AuthActionResponse:
    return AuthActionResponse(
        status="pending_wordpress",
        message=f"Password reset for {payload.email_or_username} must complete through WordPress during Phase 2.",
        redirect_url=_wordpress_reset_url(),
    )


@router.get("/social/{provider}", response_model=SocialAuthStartResponse)
async def social_start(provider: str) -> SocialAuthStartResponse:
    try:
        state = build_provider_state()
        authorization_url = get_authorization_url(provider, state)
    except UnsupportedSocialProviderError as exc:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Unsupported social provider") from exc
    return SocialAuthStartResponse(
        provider=provider,
        state=state,
        authorization_url=authorization_url,
    )


@router.post("/social/{provider}", response_model=SocialCallbackResponse)
async def social_callback(
    provider: str,
    payload: SocialCallbackRequest,
) -> SocialCallbackResponse:
    try:
        identity = exchange_callback(provider, payload.model_dump(exclude_none=True))
    except UnsupportedSocialProviderError as exc:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Unsupported social provider") from exc
    except ValueError as exc:
        raise HTTPException(status_code=status.HTTP_400_BAD_REQUEST, detail=str(exc)) from exc
    return SocialCallbackResponse(
        status="received",
        identity=SocialIdentityResult(
            provider=identity.provider,
            provider_user_id=identity.provider_user_id,
            email=identity.email,
            unionid=identity.unionid,
            openid=identity.openid,
            profile_json=identity.profile_json or {},
        ),
        linked_user_id=None,
    )
