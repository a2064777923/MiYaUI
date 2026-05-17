from __future__ import annotations

from typing import Any

from sqlalchemy import JSON, Column, Text, UniqueConstraint
from sqlmodel import Field, SQLModel

from app.models.base import SourceTraceMixin, TimestampMixin


class Comment(TimestampMixin, SourceTraceMixin, table=True):
    __tablename__ = "comments"
    __table_args__ = (UniqueConstraint("source_table", "source_id", name="uq_comments_source"),)

    id: int | None = Field(default=None, primary_key=True)
    content_item_id: int | None = Field(default=None, foreign_key="content_items.id", index=True)
    author_user_id: int | None = Field(default=None, foreign_key="user_accounts.id", index=True)
    author_name: str = Field(default="", max_length=255)
    author_email: str | None = Field(default=None, max_length=320)
    parent_source_id: str | None = Field(default=None, max_length=191, index=True)
    status: str = Field(default="approved", max_length=32, index=True)
    body: str = Field(default="", sa_column=Column(Text, nullable=False))
    meta_json: dict[str, Any] = Field(default_factory=dict, sa_column=Column(JSON, nullable=False))


class DirectMessageLegacy(TimestampMixin, SourceTraceMixin, table=True):
    __tablename__ = "direct_messages_legacy"
    __table_args__ = (UniqueConstraint("source_table", "source_id", name="uq_direct_messages_legacy_source"),)

    id: int | None = Field(default=None, primary_key=True)
    sender_source_user_id: str = Field(max_length=191, index=True)
    receiver_source_user_id: str = Field(max_length=191, index=True)
    conversation_key: str = Field(max_length=255, index=True)
    body: str = Field(default="", sa_column=Column(Text, nullable=False))
    read_at: str | None = Field(default=None, max_length=64)


class CircleTopic(TimestampMixin, SourceTraceMixin, table=True):
    __tablename__ = "circle_topics"
    __table_args__ = (UniqueConstraint("source_table", "source_id", name="uq_circle_topics_source"),)

    id: int | None = Field(default=None, primary_key=True)
    circle_source_id: str = Field(max_length=191, index=True)
    author_source_user_id: str | None = Field(default=None, max_length=191, index=True)
    title: str = Field(default="", max_length=500)
    body: str = Field(default="", sa_column=Column(Text, nullable=False))
    status: str = Field(default="publish", max_length=32, index=True)
    meta_json: dict[str, Any] = Field(default_factory=dict, sa_column=Column(JSON, nullable=False))


class QuestionAnswer(TimestampMixin, SourceTraceMixin, table=True):
    __tablename__ = "question_answers"
    __table_args__ = (UniqueConstraint("source_table", "source_id", name="uq_question_answers_source"),)

    id: int | None = Field(default=None, primary_key=True)
    content_item_id: int | None = Field(default=None, foreign_key="content_items.id", index=True)
    entry_type: str = Field(default="question", max_length=32, index=True)
    question_source_id: str = Field(max_length=191, index=True)
    answer_source_id: str | None = Field(default=None, max_length=191, index=True)
    author_source_user_id: str | None = Field(default=None, max_length=191, index=True)
    accepted: bool = Field(default=False, index=True)
    body: str = Field(default="", sa_column=Column(Text, nullable=False))
    meta_json: dict[str, Any] = Field(default_factory=dict, sa_column=Column(JSON, nullable=False))


COMMUNITY_MODELS: tuple[type[SQLModel], ...] = (Comment, DirectMessageLegacy, CircleTopic, QuestionAnswer)
