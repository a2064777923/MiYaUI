from fastapi import APIRouter, Depends
from sqlalchemy import text
from sqlalchemy.ext.asyncio import AsyncSession

from app.core.database import get_db

router = APIRouter()


@router.get("/health")
async def health_check():
    return {"status": "ok"}


@router.get("/health/db")
async def db_check(db: AsyncSession = Depends(get_db)):
    result = await db.execute(text("SELECT 1 as healthy"))
    return {"database": "connected", "result": result.scalar()}
