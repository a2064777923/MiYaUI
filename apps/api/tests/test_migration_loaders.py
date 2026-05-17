import pytest
from sqlalchemy import select

from app.migration.loaders import load_content_payloads, load_dataset, load_user_payloads
from app.models.content import ContentItem, ContentMeta, ContentTerm
from app.models.users import SocialIdentity, UserAccount, UserRole


@pytest.mark.asyncio
async def test_content_loader_is_idempotent(db_session):
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
        "meta_json": {"views": 1},
        "search_text": "Hello",
        "render_mode": "ssr",
        "fallback_required": False,
        "metas": [
            {
                "meta_key": "views",
                "raw_value": "1",
                "parsed_value": {"value": "1"},
                "source_table": "wp_postmeta",
                "source_id": "1",
                "source_updated_at": None,
            }
        ],
        "terms": [
            {
                "source_table": "wp_term_relationships",
                "source_id": "123:7",
                "taxonomy": {
                    "source_system": "wordpress",
                    "source_table": "wp_term_taxonomy",
                    "source_id": "7",
                    "source_updated_at": None,
                    "taxonomy": "category",
                    "description": "",
                    "parent_source_id": None,
                    "count": 1,
                },
                "term": {
                    "source_system": "wordpress",
                    "source_table": "wp_terms",
                    "source_id": "3",
                    "source_updated_at": None,
                    "slug": "design",
                    "name": "Design",
                    "description": "",
                },
                "source_updated_at": None,
            }
        ],
    }
    first = await load_content_payloads(db_session, [payload])
    second_payload = dict(payload)
    second_payload["title"] = "Hello Updated"
    second = await load_content_payloads(db_session, [second_payload])
    await db_session.commit()

    items = (await db_session.execute(select(ContentItem))).scalars().all()
    assert len(items) == 1
    assert items[0].title == "Hello Updated"
    assert first.inserted_count == 1
    assert second.updated_count == 1

    metas = (await db_session.execute(select(ContentMeta))).scalars().all()
    terms = (await db_session.execute(select(ContentTerm))).scalars().all()
    assert len(metas) == 1
    assert len(terms) == 1


@pytest.mark.asyncio
async def test_user_loader_replaces_roles_and_social_identities(db_session):
    payload = {
        "source_system": "wordpress",
        "source_table": "wp_users",
        "source_id": "8",
        "source_updated_at": None,
        "wordpress_user_id": 8,
        "username": "alice",
        "email": "alice@example.com",
        "nicename": "alice",
        "display_name": "Alice",
        "role_label": "admin",
        "password_authority": "wordpress",
        "first_relogin_required": True,
        "meta_json": {},
        "profile": {"avatar_url": "https://img", "phone": "123", "bio": "bio", "meta_json": {}},
        "roles": [
            {
                "role_name": "administrator",
                "capability_json": {"administrator": True},
                "source_id": "8:administrator",
            }
        ],
        "social_identities": [{"provider": "weixin", "provider_user_id": "wx-1", "source_id": "8:weixin"}],
    }
    await load_user_payloads(db_session, [payload])
    await load_user_payloads(db_session, [payload])
    await db_session.commit()

    users = (await db_session.execute(select(UserAccount))).scalars().all()
    roles = (await db_session.execute(select(UserRole))).scalars().all()
    social = (await db_session.execute(select(SocialIdentity))).scalars().all()
    assert len(users) == 1
    assert len(roles) == 1
    assert len(social) == 1


@pytest.mark.asyncio
async def test_dataset_dispatch_supports_commerce_legacy(db_session):
    payload = [
        {
            "source_system": "wordpress",
            "source_table": "wp_zrz_order",
            "source_id": "1",
            "source_updated_at": None,
            "order_no": "A001",
            "user_source_id": "8",
            "amount": 99.0,
            "currency": "CNY",
            "status": "paid",
            "payment_gateway": "wechat",
            "paid_at": None,
            "authoritative_system": "wordpress",
            "is_read_only": True,
            "meta_json": {},
        }
    ]
    result = await load_dataset(db_session, "commerce-legacy", payload)
    assert result.inserted_count == 1
