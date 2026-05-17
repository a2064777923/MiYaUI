import pytest

from app.migration.loaders import load_content_payloads, load_user_payloads


async def seed_content(db_session):
    await load_user_payloads(
        db_session,
        [
            {
                "source_system": "wordpress",
                "source_table": "wp_users",
                "source_id": "8",
                "source_updated_at": None,
                "wordpress_user_id": 8,
                "username": "alice",
                "email": "alice@example.com",
                "nicename": "alice",
                "display_name": "Alice",
                "role_label": "member",
                "password_authority": "wordpress",
                "first_relogin_required": False,
                "meta_json": {},
                "profile": {},
                "roles": [],
                "social_identities": [],
            }
        ],
    )
    await load_content_payloads(
        db_session,
        [
            {
                "source_system": "wordpress",
                "source_table": "wp_posts",
                "source_id": "123",
                "source_updated_at": None,
                "wordpress_post_id": 123,
                "author_source_id": "8",
                "content_type": "post",
                "status": "publish",
                "slug": "hello",
                "title": "Hello",
                "body": "<p>Hello</p>",
                "excerpt": "Hello",
                "published_at": None,
                "content_updated_at": None,
                "original_url_path": "/123.html",
                "seo_title": "SEO Hello",
                "seo_description": "SEO Description",
                "seo_json": {"title": "SEO Hello"},
                "meta_json": {"author_nicename": "alice"},
                "search_text": "Hello",
                "render_mode": "ssr",
                "fallback_required": False,
                "metas": [],
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
                            "description": "Design content",
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
        ],
    )
    await db_session.commit()


@pytest.mark.asyncio
async def test_content_resolve_endpoint_returns_detail(client, db_session):
    await seed_content(db_session)
    response = await client.get("/api/v1/content/resolve", params={"path": "/123.html"})
    assert response.status_code == 200
    payload = response.json()
    assert payload["status"] == "ok"
    assert payload["view"] == "detail"
    assert payload["item"]["title"] == "Hello"


@pytest.mark.asyncio
async def test_content_taxonomy_and_author_routes(client, db_session):
    await seed_content(db_session)

    taxonomy_response = await client.get("/api/v1/content/taxonomies/category/design")
    assert taxonomy_response.status_code == 200
    assert taxonomy_response.json()["term"]["slug"] == "design"

    author_response = await client.get("/api/v1/content/authors/alice")
    assert author_response.status_code == 200
    assert author_response.json()["author"]["username"] == "alice"


@pytest.mark.asyncio
async def test_content_search_and_sitemap(client, db_session):
    await seed_content(db_session)

    search_response = await client.get("/api/v1/content/search", params={"q": "Hello"})
    assert search_response.status_code == 200
    assert search_response.json()["total"] == 1

    sitemap_response = await client.get("/api/v1/content/sitemap")
    assert sitemap_response.status_code == 200
    assert sitemap_response.json()[0]["path"] == "/123.html"
