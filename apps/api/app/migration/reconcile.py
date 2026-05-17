from __future__ import annotations

import json
from dataclasses import dataclass
from pathlib import Path
from typing import Any

from sqlalchemy import select
from sqlalchemy.ext.asyncio import AsyncSession

from app.models.commerce_legacy import LegacyGoldTransaction, LegacyOrder, LegacyVipMembership
from app.models.community import CircleTopic, Comment, DirectMessageLegacy, QuestionAnswer
from app.models.content import ContentItem
from app.models.media import MediaAsset
from app.models.users import UserAccount
from app.schemas.migration import ReconciliationSummary

TARGET_MODELS = {
    "content": ContentItem,
    "users": UserAccount,
    "community": (Comment, DirectMessageLegacy, CircleTopic, QuestionAnswer),
    "commerce-legacy": (LegacyOrder, LegacyGoldTransaction, LegacyVipMembership),
    "media": MediaAsset,
}


@dataclass
class ReconciliationReport:
    summaries: list[ReconciliationSummary]

    @property
    def has_mismatch(self) -> bool:
        return any(not summary.matched for summary in self.summaries)

    def to_markdown(self) -> str:
        lines = [
            "# Reconciliation Report",
            "",
            "| Dataset | Source | Target | Samples | Mismatches | Status |",
            "|---------|--------|--------|---------|------------|--------|",
        ]
        for summary in self.summaries:
            status = "PASS" if summary.matched else "FAIL"
            lines.append(
                f"| {summary.dataset} | {summary.source_count} | {summary.target_count} | {summary.sample_size} | "
                f"{summary.mismatch_count} | {status} |"
            )
        return "\n".join(lines) + "\n"

    def write(self, output_path: Path) -> None:
        output_path.parent.mkdir(parents=True, exist_ok=True)
        output_path.write_text(self.to_markdown(), encoding="utf-8")
        json_path = output_path.with_suffix(".json")
        json_path.write_text(
            json.dumps([summary.model_dump() for summary in self.summaries], ensure_ascii=False, indent=2),
            encoding="utf-8",
        )


async def _count_model(session: AsyncSession, model) -> int:
    result = await session.execute(select(model))
    return len(result.scalars().all())


async def _content_samples(
    session: AsyncSession,
    source_payloads: list[dict[str, Any]],
    sample_size: int,
) -> list[dict[str, Any]]:
    payload_index = {payload["source_id"]: payload for payload in source_payloads[:sample_size]}
    if not payload_index:
        return []
    result = await session.execute(select(ContentItem).where(ContentItem.source_id.in_(payload_index.keys())))
    comparisons = []
    for item in result.scalars().all():
        source = payload_index[item.source_id]
        mismatches = []
        if item.title != source["title"]:
            mismatches.append("title")
        if item.status != source["status"]:
            mismatches.append("status")
        if item.content_type != source["content_type"]:
            mismatches.append("content_type")
        comparisons.append({"source_id": item.source_id, "mismatches": mismatches})
    return comparisons


async def _user_samples(
    session: AsyncSession,
    source_payloads: list[dict[str, Any]],
    sample_size: int,
) -> list[dict[str, Any]]:
    payload_index = {payload["source_id"]: payload for payload in source_payloads[:sample_size]}
    if not payload_index:
        return []
    result = await session.execute(select(UserAccount).where(UserAccount.source_id.in_(payload_index.keys())))
    comparisons = []
    for item in result.scalars().all():
        source = payload_index[item.source_id]
        mismatches = []
        if item.username != source["username"]:
            mismatches.append("username")
        if item.email != source["email"]:
            mismatches.append("email")
        if item.role_label != source["role_label"]:
            mismatches.append("role_label")
        comparisons.append({"source_id": item.source_id, "mismatches": mismatches})
    return comparisons


async def reconcile_dataset(
    session: AsyncSession,
    *,
    dataset: str,
    source_payloads: list[dict[str, Any]],
    source_count: int,
    sample_size: int,
) -> ReconciliationSummary:
    target_models = TARGET_MODELS[dataset]
    if isinstance(target_models, tuple):
        target_count = 0
        for model in target_models:
            target_count += await _count_model(session, model)
    else:
        target_count = await _count_model(session, target_models)

    mismatch_details: list[dict[str, Any]] = []
    if dataset == "content":
        mismatch_details = await _content_samples(session, source_payloads, sample_size)
    elif dataset == "users":
        mismatch_details = await _user_samples(session, source_payloads, sample_size)

    mismatch_count = len([item for item in mismatch_details if item["mismatches"]]) + (
        1 if source_count != target_count else 0
    )
    return ReconciliationSummary(
        dataset=dataset,
        source_count=source_count,
        target_count=target_count,
        sample_size=min(sample_size, len(source_payloads)),
        mismatch_count=mismatch_count,
        matched=mismatch_count == 0,
        details={"comparisons": mismatch_details},
    )
