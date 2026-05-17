from collections.abc import AsyncGenerator

from sqlalchemy.engine import make_url
from sqlalchemy.ext.asyncio import AsyncSession, async_sessionmaker, create_async_engine

from app.config import settings


def _connect_args(database_url: str) -> dict[str, object]:
    backend = make_url(database_url).get_backend_name()
    if backend == "sqlite":
        return {"check_same_thread": False}
    return {}


def configure_engine(database_url: str | None = None):
    global engine, async_session

    resolved_url = database_url or settings.DATABASE_URL
    engine = create_async_engine(
        resolved_url,
        echo=settings.SQL_ECHO,
        connect_args=_connect_args(resolved_url),
    )
    async_session = async_sessionmaker(engine, class_=AsyncSession, expire_on_commit=False)
    return engine


engine = configure_engine()


async def get_db() -> AsyncGenerator[AsyncSession]:
    async with async_session() as session:
        try:
            yield session
            await session.commit()
        except Exception:
            await session.rollback()
            raise
