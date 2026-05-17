from __future__ import annotations

from dataclasses import dataclass
from typing import Any

from sqlalchemy.ext.asyncio import AsyncSession

from app.crud.common import upsert_by_source
from app.crud.content import (
    get_content_item_by_source_id,
    replace_content_meta,
    replace_content_terms,
    upsert_content_item,
)
from app.crud.users import replace_social_identities, replace_user_profile, replace_user_roles, upsert_user_account
from app.models.commerce_legacy import LegacyGoldTransaction, LegacyOrder, LegacyVipMembership
from app.models.community import CircleTopic, Comment, DirectMessageLegacy, QuestionAnswer
from app.models.media import MediaAsset


@dataclass
class LoadResult:
    source_count: int = 0
    inserted_count: int = 0
    updated_count: int = 0
    failed_count: int = 0

    @property
    def records_written(self) -> int:
        return self.inserted_count + self.updated_count


async def load_content_payloads(session: AsyncSession, payloads: list[dict[str, Any]]) -> LoadResult:
    result = LoadResult(source_count=len(payloads))
    for payload in payloads:
        content_payload = {key: value for key, value in payload.items() if key not in {"metas", "terms"}}
        content, created = await upsert_content_item(session, content_payload)
        await replace_content_meta(session, content, payload.get("metas", []))
        await replace_content_terms(session, content, payload.get("terms", []))
        if created:
            result.inserted_count += 1
        else:
            result.updated_count += 1
    return result


async def load_user_payloads(session: AsyncSession, payloads: list[dict[str, Any]]) -> LoadResult:
    result = LoadResult(source_count=len(payloads))
    for payload in payloads:
        account_payload = {
            key: value
            for key, value in payload.items()
            if key not in {"profile", "roles", "social_identities"}
        }
        user, created = await upsert_user_account(session, account_payload)
        await replace_user_profile(session, user, payload.get("profile", {}))
        await replace_user_roles(session, user, payload.get("roles", []))
        await replace_social_identities(session, user, payload.get("social_identities", []))
        if created:
            result.inserted_count += 1
        else:
            result.updated_count += 1
    return result


async def _resolve_content_id(session: AsyncSession, source_id: str | None) -> int | None:
    if not source_id:
        return None
    content = await get_content_item_by_source_id(session, source_id)
    return content.id if content else None


async def load_community_payloads(session: AsyncSession, payloads: list[dict[str, Any]]) -> LoadResult:
    result = LoadResult(source_count=len(payloads))
    for payload in payloads:
        source_table = payload["source_table"]
        if source_table.endswith("comments"):
            model = Comment
            payload = dict(payload)
            payload["content_item_id"] = await _resolve_content_id(session, payload.pop("content_item_source_id", None))
        elif source_table.endswith("zrz_directmessage"):
            model = DirectMessageLegacy
        elif payload.get("entry_type"):
            model = QuestionAnswer
            payload = dict(payload)
            payload["content_item_id"] = await _resolve_content_id(session, payload.pop("content_item_source_id", None))
        else:
            model = CircleTopic
        _, created = await upsert_by_source(session, model, payload)
        if created:
            result.inserted_count += 1
        else:
            result.updated_count += 1
    return result


async def load_commerce_payloads(session: AsyncSession, payloads: list[dict[str, Any]]) -> LoadResult:
    result = LoadResult(source_count=len(payloads))
    for payload in payloads:
        if payload["source_table"].endswith("zrz_order"):
            model = LegacyOrder
        elif payload["source_table"].endswith("b2_gold"):
            model = LegacyGoldTransaction
        else:
            model = LegacyVipMembership
        _, created = await upsert_by_source(session, model, payload)
        if created:
            result.inserted_count += 1
        else:
            result.updated_count += 1
    return result


async def load_media_payloads(session: AsyncSession, payloads: list[dict[str, Any]]) -> LoadResult:
    result = LoadResult(source_count=len(payloads))
    for payload in payloads:
        media_payload = dict(payload)
        media_payload["content_item_id"] = await _resolve_content_id(session, payload.get("content_item_source_id"))
        media_payload.pop("content_item_source_id", None)
        _, created = await upsert_by_source(session, MediaAsset, media_payload)
        if created:
            result.inserted_count += 1
        else:
            result.updated_count += 1
    return result


async def load_dataset(session: AsyncSession, dataset: str, payloads: list[dict[str, Any]]) -> LoadResult:
    if dataset == "content":
        return await load_content_payloads(session, payloads)
    if dataset == "users":
        return await load_user_payloads(session, payloads)
    if dataset == "community":
        return await load_community_payloads(session, payloads)
    if dataset == "commerce-legacy":
        return await load_commerce_payloads(session, payloads)
    if dataset == "media":
        return await load_media_payloads(session, payloads)
    raise ValueError(f"Unsupported dataset: {dataset}")
