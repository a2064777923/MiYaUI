from __future__ import annotations

from fastapi import APIRouter, Depends
from sqlalchemy import select
from sqlalchemy.exc import OperationalError, ProgrammingError
from sqlalchemy.ext.asyncio import AsyncSession

from app.core.database import get_db
from app.models import PHASE_02_TABLES
from app.models.migration import MigrationRun
from app.schemas.migration import MigrationRunRead

router = APIRouter(prefix="/migration", tags=["migration"])


@router.get("/schema")
async def get_schema() -> dict[str, object]:
    table_names = sorted(table.name for table in PHASE_02_TABLES)
    return {"tables": table_names, "count": len(table_names)}


@router.get("/runs", response_model=list[MigrationRunRead])
async def get_migration_runs(db: AsyncSession = Depends(get_db)) -> list[MigrationRunRead]:
    try:
        result = await db.execute(select(MigrationRun).order_by(MigrationRun.started_at.desc()).limit(20))
    except (OperationalError, ProgrammingError):
        return []
    return [MigrationRunRead.model_validate(row, from_attributes=True) for row in result.scalars().all()]
