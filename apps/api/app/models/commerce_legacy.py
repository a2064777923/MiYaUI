from __future__ import annotations

from datetime import datetime
from typing import Any

from sqlalchemy import JSON, Column, Numeric, Text, UniqueConstraint
from sqlmodel import Field, SQLModel

from app.models.base import SourceTraceMixin, TimestampMixin


class LegacyOrder(TimestampMixin, SourceTraceMixin, table=True):
    __tablename__ = "legacy_orders"
    __table_args__ = (UniqueConstraint("source_table", "source_id", name="uq_legacy_orders_source"),)

    id: int | None = Field(default=None, primary_key=True)
    order_no: str | None = Field(default=None, max_length=191, index=True)
    user_source_id: str | None = Field(default=None, max_length=191, index=True)
    amount: float = Field(default=0, sa_column=Column(Numeric(10, 2), nullable=False))
    currency: str = Field(default="CNY", max_length=16)
    status: str = Field(default="pending", max_length=64, index=True)
    payment_gateway: str | None = Field(default=None, max_length=64)
    paid_at: datetime | None = Field(default=None, index=True)
    authoritative_system: str = Field(default="wordpress", max_length=64)
    is_read_only: bool = Field(default=True, index=True)
    meta_json: dict[str, Any] = Field(default_factory=dict, sa_column=Column(JSON, nullable=False))


class LegacyGoldTransaction(TimestampMixin, SourceTraceMixin, table=True):
    __tablename__ = "legacy_gold_transactions"
    __table_args__ = (UniqueConstraint("source_table", "source_id", name="uq_legacy_gold_transactions_source"),)

    id: int | None = Field(default=None, primary_key=True)
    user_source_id: str | None = Field(default=None, max_length=191, index=True)
    amount: float = Field(default=0, sa_column=Column(Numeric(10, 2), nullable=False))
    balance_after: float | None = Field(default=None, sa_column=Column(Numeric(10, 2), nullable=True))
    reason: str | None = Field(default=None, max_length=255)
    note: str = Field(default="", sa_column=Column(Text, nullable=False))
    is_read_only: bool = Field(default=True, index=True)


class LegacyVipMembership(TimestampMixin, SourceTraceMixin, table=True):
    __tablename__ = "legacy_vip_memberships"
    __table_args__ = (UniqueConstraint("source_table", "source_id", name="uq_legacy_vip_memberships_source"),)

    id: int | None = Field(default=None, primary_key=True)
    user_source_id: str | None = Field(default=None, max_length=191, index=True)
    plan_name: str | None = Field(default=None, max_length=255)
    status: str = Field(default="inactive", max_length=64, index=True)
    starts_at: datetime | None = Field(default=None, index=True)
    ends_at: datetime | None = Field(default=None, index=True)
    is_read_only: bool = Field(default=True, index=True)
    meta_json: dict[str, Any] = Field(default_factory=dict, sa_column=Column(JSON, nullable=False))


COMMERCE_LEGACY_MODELS: tuple[type[SQLModel], ...] = (LegacyOrder, LegacyGoldTransaction, LegacyVipMembership)
