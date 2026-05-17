import pytest

from app.migration.loaders import load_content_payloads, load_user_payloads
from app.migration.reconcile import reconcile_dataset


@pytest.mark.asyncio
async def test_reconcile_dataset_passes_when_counts_and_samples_match(db_session):
    payload = {
        "source_system": "wordpress",
        "source_table": "wp_posts",
        "source_id": "123",
        "source_updated_at": None,
        "wordpress_post_id": 123,
        "author_source_id": "1",
        "content_type": "post",
        "status": "publish",
        "slug": "hello",
        "title": "Hello",
        "body": "<p>Hello</p>",
        "excerpt": "Hello",
        "published_at": None,
        "content_updated_at": None,
        "original_url_path": "/123.html",
        "seo_title": "Hello",
        "seo_description": "Hello",
        "seo_json": {"title": "Hello"},
        "meta_json": {},
        "search_text": "Hello",
        "render_mode": "ssr",
        "fallback_required": False,
        "metas": [],
        "terms": [],
    }
    await load_content_payloads(db_session, [payload])
    await db_session.commit()

    summary = await reconcile_dataset(
        db_session,
        dataset="content",
        source_payloads=[payload],
        source_count=1,
        sample_size=5,
    )
    assert summary.matched is True
    assert summary.mismatch_count == 0


@pytest.mark.asyncio
async def test_reconcile_dataset_detects_mismatch(db_session):
    payload = {
        "source_system": "wordpress",
        "source_table": "wp_users",
        "source_id": "1",
        "source_updated_at": None,
        "wordpress_user_id": 1,
        "username": "alice",
        "email": "alice@example.com",
        "nicename": "alice",
        "display_name": "Alice",
        "role_label": "member",
        "password_authority": "wordpress",
        "first_relogin_required": True,
        "meta_json": {},
        "profile": {},
        "roles": [],
        "social_identities": [],
    }
    await load_user_payloads(db_session, [payload])
    await db_session.commit()
    payload["email"] = "changed@example.com"

    summary = await reconcile_dataset(
        db_session,
        dataset="users",
        source_payloads=[payload],
        source_count=1,
        sample_size=5,
    )
    assert summary.matched is False
    assert summary.mismatch_count >= 1
