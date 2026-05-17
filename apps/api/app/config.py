from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    PROJECT_NAME: str = "MiyaUI API"
    DATABASE_URL: str = "postgresql+asyncpg://miyaui:miyaui@localhost:5432/miyaui"
    REDIS_URL: str = "redis://localhost:6379/0"
    CORS_ORIGINS: list[str] = ["*"]
    PUBLIC_SITE_URL: str = "http://localhost:3001"
    FASTAPI_PUBLIC_URL: str = "http://localhost:8001"
    APP_SECRET_KEY: str = "miyaui-local-dev-secret"
    SQL_ECHO: bool = False
    AUTH_SESSION_COOKIE_NAME: str = "miyaui_session"
    AUTH_SESSION_TTL_SECONDS: int = 604800
    AUTH_SESSION_COOKIE_SECURE: bool = False
    AUTH_SESSION_COOKIE_SAMESITE: str = "lax"
    AUTH_SESSION_COOKIE_DOMAIN: str | None = None
    WORDPRESS_BASE_URL: str = "http://localhost:8082"
    WORDPRESS_AUTH_BRIDGE_PATH: str = "/wp-json/miyaui/v1/auth/verify"
    WORDPRESS_AUTH_BRIDGE_SECRET: str = "miyaui-local-bridge-secret"
    WORDPRESS_AUTH_BRIDGE_TIMEOUT_SECONDS: int = 10
    WORDPRESS_DB_HOST: str = "localhost"
    WORDPRESS_DB_PORT: int = 4306
    WORDPRESS_DB_NAME: str = "www_miyaui_com_dev"
    WORDPRESS_DB_USER: str = "www_miyaui_com_dev"
    WORDPRESS_DB_PASSWORD: str = "miyaui_local_wp"
    WORDPRESS_TABLE_PREFIX: str = "wp_"
    SOCIAL_CALLBACK_BASE_URL: str = "http://localhost:3001/api/auth/social"

    model_config = SettingsConfigDict(env_file=".env", env_file_encoding="utf-8", extra="ignore")


settings = Settings()
