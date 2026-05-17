from __future__ import annotations

from typing import Any

from sqlalchemy import select
from sqlalchemy.ext.asyncio import AsyncSession
from sqlmodel import SQLModel


def model_payload(model: type[SQLModel], payload: dict[str, Any]) -> dict[str, Any]:
    return {key: value for key, value in payload.items() if key in model.model_fields}


async def upsert_by_source(
    session: AsyncSession,
    model: type[SQLModel],
    payload: dict[str, Any],
) -> tuple[SQLModel, bool]:
    source_table = payload["source_table"]
    source_id = payload["source_id"]
    statement = select(model).where(model.source_table == source_table, model.source_id == source_id)  # type: ignore[attr-defined]
    result = await session.execute(statement)
    instance = result.scalar_one_or_none()
    created = instance is None

    if instance is None:
        instance = model(**model_payload(model, payload))
        session.add(instance)
    else:
        for key, value in model_payload(model, payload).items():
            setattr(instance, key, value)

    await session.flush()
    return instance, created
