from sqlmodel import SQLModel

from app.models import (
    PHASE_02_TABLES,
    LegacyGoldTransaction,
    LegacyOrder,
    LegacyVipMembership,
    MediaAsset,
)
from app.models.content import ContentItem
from app.models.users import SocialIdentity, UserAccount


def _field_names(model) -> set[str]:
    return set(model.model_fields)


def test_phase_02_tables_are_registered():
    table_names = {table.name for table in PHASE_02_TABLES}
    assert "content_items" in table_names
    assert "user_accounts" in table_names
    assert "migration_runs" in table_names
    assert "media_assets" in table_names
    assert "legacy_orders" in table_names


def test_core_models_include_source_traceability_fields():
    expected = {"source_system", "source_table", "source_id", "source_updated_at"}
    for model in (ContentItem, UserAccount, SocialIdentity, MediaAsset):
        assert expected.issubset(_field_names(model))


def test_legacy_commerce_models_are_read_only_mirrors():
    for model in (LegacyOrder, LegacyGoldTransaction, LegacyVipMembership):
        assert "is_read_only" in _field_names(model)
        assert model.model_fields["is_read_only"].default is True


def test_media_model_preserves_existing_source_location_fields():
    media_fields = _field_names(MediaAsset)
    assert {"source_url", "source_path", "attachment_source_id"}.issubset(media_fields)


def test_sqlmodel_metadata_contains_phase_02_tables():
    metadata_tables = set(SQLModel.metadata.tables)
    assert "content_items" in metadata_tables
    assert "user_accounts" in metadata_tables
