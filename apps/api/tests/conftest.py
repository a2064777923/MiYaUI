import os
import tempfile
from pathlib import Path

import pytest
from httpx import ASGITransport, AsyncClient
from sqlmodel import SQLModel

TEST_DB_PATH = Path(os.environ.get("TEST_DB_PATH", Path(tempfile.gettempdir()) / "miyaui-test.db"))
os.environ["DATABASE_URL"] = f"sqlite+aiosqlite:///{TEST_DB_PATH.as_posix()}"
os.environ.setdefault("APP_SECRET_KEY", "test-secret")

# ruff: noqa: E402
from app.core import database
from app.main import app
from app.models import PHASE_02_MODELS  # noqa: F401


@pytest.fixture(scope="session", autouse=True)
async def setup_database():
    if TEST_DB_PATH.exists():
        TEST_DB_PATH.unlink()
    database.configure_engine(os.environ["DATABASE_URL"])
    async with database.engine.begin() as connection:
        await connection.run_sync(SQLModel.metadata.drop_all)
        await connection.run_sync(SQLModel.metadata.create_all)
    yield
    await database.engine.dispose()
    if TEST_DB_PATH.exists():
        TEST_DB_PATH.unlink()


@pytest.fixture(autouse=True)
async def reset_database(setup_database):
    async with database.engine.begin() as connection:
        await connection.run_sync(SQLModel.metadata.drop_all)
        await connection.run_sync(SQLModel.metadata.create_all)
    yield


@pytest.fixture
async def db_session(reset_database):
    async with database.async_session() as session:
        yield session
        await session.rollback()


@pytest.fixture
async def client(reset_database):
    transport = ASGITransport(app=app)
    async with AsyncClient(transport=transport, base_url="http://test") as ac:
        yield ac
