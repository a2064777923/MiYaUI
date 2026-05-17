from sqlmodel import SQLModel

from app.models.commerce_legacy import COMMERCE_LEGACY_MODELS, LegacyGoldTransaction, LegacyOrder, LegacyVipMembership
from app.models.community import COMMUNITY_MODELS, CircleTopic, Comment, DirectMessageLegacy, QuestionAnswer
from app.models.content import CONTENT_MODELS, ContentItem, ContentMeta, ContentTerm, Taxonomy, Term
from app.models.media import MEDIA_MODELS, MediaAsset
from app.models.migration import (
    MIGRATION_MODELS,
    MigrationDatasetStat,
    MigrationRun,
    MigrationSourceCheckpoint,
    ReconciliationIssue,
)
from app.models.users import (
    USER_MODELS,
    SessionRefreshToken,
    SocialIdentity,
    UserAccount,
    UserProfile,
    UserRole,
)

PHASE_02_MODELS: tuple[type[SQLModel], ...] = (
    *CONTENT_MODELS,
    *USER_MODELS,
    *COMMUNITY_MODELS,
    *COMMERCE_LEGACY_MODELS,
    *MEDIA_MODELS,
    *MIGRATION_MODELS,
)

PHASE_02_TABLES = tuple(model.__table__ for model in PHASE_02_MODELS)

__all__ = [
    "CircleTopic",
    "Comment",
    "ContentItem",
    "ContentMeta",
    "ContentTerm",
    "DirectMessageLegacy",
    "LegacyGoldTransaction",
    "LegacyOrder",
    "LegacyVipMembership",
    "MediaAsset",
    "MigrationDatasetStat",
    "MigrationRun",
    "MigrationSourceCheckpoint",
    "PHASE_02_MODELS",
    "PHASE_02_TABLES",
    "QuestionAnswer",
    "ReconciliationIssue",
    "SessionRefreshToken",
    "SocialIdentity",
    "Taxonomy",
    "Term",
    "UserAccount",
    "UserProfile",
    "UserRole",
]
