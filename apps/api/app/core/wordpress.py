from __future__ import annotations

from dataclasses import dataclass

from app.config import settings


@dataclass(frozen=True)
class WordPressDatabaseConfig:
    host: str
    port: int
    database: str
    username: str
    password: str
    table_prefix: str


@dataclass(frozen=True)
class WordPressBridgeConfig:
    base_url: str
    verify_path: str
    secret: str

    @property
    def verify_url(self) -> str:
        return f"{self.base_url.rstrip('/')}{self.verify_path}"


def get_wordpress_database_config() -> WordPressDatabaseConfig:
    return WordPressDatabaseConfig(
        host=settings.WORDPRESS_DB_HOST,
        port=settings.WORDPRESS_DB_PORT,
        database=settings.WORDPRESS_DB_NAME,
        username=settings.WORDPRESS_DB_USER,
        password=settings.WORDPRESS_DB_PASSWORD,
        table_prefix=settings.WORDPRESS_TABLE_PREFIX,
    )


def get_wordpress_bridge_config() -> WordPressBridgeConfig:
    return WordPressBridgeConfig(
        base_url=settings.WORDPRESS_BASE_URL,
        verify_path=settings.WORDPRESS_AUTH_BRIDGE_PATH,
        secret=settings.WORDPRESS_AUTH_BRIDGE_SECRET,
    )


def build_wordpress_auth_headers() -> dict[str, str]:
    return {"X-MIYAUI-BRIDGE-SECRET": settings.WORDPRESS_AUTH_BRIDGE_SECRET}
