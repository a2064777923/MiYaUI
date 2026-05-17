from app.migration.extractors import WordPressExtractor, map_wordpress_roles
from app.migration.mysql import QuerySpec


class FakeClient:
    def __init__(self, responses):
        self.responses = responses
        self.count_calls = []
        self.count_responses = {}
        self.config = type(
            "Config",
            (),
            {
                "table_prefix": "wp_",
            },
        )()

    def query_spec(self, spec: QuerySpec):
        for key, value in self.responses.items():
            if key in spec.sql:
                return value
        return []

    def count(self, table_name: str, where_sql: str = "", params=None):
        self.count_calls.append((table_name, where_sql, params))
        return self.count_responses.get((table_name, where_sql), 0)


def test_build_posts_query_uses_table_prefix_and_filters():
    extractor = WordPressExtractor(FakeClient({}))
    spec = extractor.build_posts_query(limit=10)
    assert "FROM wp_posts" in spec.sql
    assert "post_type IN" in spec.sql
    assert spec.params[-1] == 10


def test_source_count_for_content_matches_public_content_filters():
    client = FakeClient({})
    extractor = WordPressExtractor(client)
    assert extractor.source_count_for_dataset("content") == 0
    table_name, where_sql, params = client.count_calls[0]
    assert table_name == "wp_posts"
    assert "post_type IN" in where_sql
    assert "post_status='publish'" in where_sql
    assert "attachment" not in params


def test_source_count_for_community_matches_extracted_sources():
    client = FakeClient({})
    client.count_responses = {
        ("wp_comments", "comment_type='comment'"): 2,
        ("wp_zrz_directmessage", ""): 3,
        ("wp_posts", "post_type='circle' AND post_status='publish'"): 5,
        ("wp_posts", "post_type IN (%s, %s, %s) AND post_status='publish'"): 7,
    }
    extractor = WordPressExtractor(client)
    assert extractor.source_count_for_dataset("community") == 17
    assert ("wp_comments", "comment_type='comment'", None) in client.count_calls
    assert ("wp_zrz_directmessage", "", None) in client.count_calls
    assert ("wp_posts", "post_type='circle' AND post_status='publish'", None) in client.count_calls
    assert client.count_calls[-1] == (
        "wp_posts",
        "post_type IN (%s, %s, %s) AND post_status='publish'",
        ("ask", "answer", "question"),
    )


def test_source_count_for_commerce_legacy_includes_vip_cards():
    client = FakeClient({})
    client.count_responses = {
        ("wp_zrz_order", ""): 11,
        ("wp_b2_gold", ""): 13,
        ("wp_zrz_card", ""): 17,
    }
    extractor = WordPressExtractor(client)
    assert extractor.source_count_for_dataset("commerce-legacy") == 41
    assert client.count_calls == [
        ("wp_zrz_order", "", None),
        ("wp_b2_gold", "", None),
        ("wp_zrz_card", "", None),
    ]


def test_extract_content_normalizes_meta_and_taxonomy_relationships():
    client = FakeClient(
        {
            "FROM wp_posts": [
                {
                    "ID": 123,
                    "post_author": 8,
                    "post_date": "2026-05-01 12:00:00",
                    "post_modified": "2026-05-02 12:00:00",
                    "post_content": '<p>Hello <img src="/uploads/a.jpg"></p>',
                    "post_title": "Hello",
                    "post_excerpt": "",
                    "post_status": "publish",
                    "post_name": "hello",
                    "post_parent": 0,
                    "guid": "https://www.miyaui.com/?p=123",
                    "menu_order": 0,
                    "post_type": "post",
                    "post_mime_type": "",
                    "comment_count": 2,
                }
            ],
            "FROM wp_postmeta": [
                {"meta_id": 1, "post_id": 123, "meta_key": "_yoast_wpseo_title", "meta_value": "SEO Hello"},
                {
                    "meta_id": 2,
                    "post_id": 123,
                    "meta_key": "_wp_attachment_metadata",
                    "meta_value": 'a:1:{s:5:"width";i:100;}',
                },
            ],
            "JOIN wp_term_taxonomy": [
                {
                    "object_id": 123,
                    "term_taxonomy_id": 7,
                    "term_id": 3,
                    "taxonomy": "category",
                    "description": "Design",
                    "parent": 0,
                    "count": 5,
                    "name": "Design",
                    "slug": "design",
                }
            ],
        }
    )
    extractor = WordPressExtractor(client)
    payloads = extractor.extract_content()
    assert len(payloads) == 1
    payload = payloads[0]
    assert payload["original_url_path"] == "/123.html"
    assert payload["seo_title"] == "SEO Hello"
    assert payload["metas"][1]["parsed_value"]["is_serialized"] is True
    assert payload["terms"][0]["taxonomy"]["taxonomy"] == "category"


def test_extract_users_maps_roles_and_social_ids():
    client = FakeClient(
        {
            "FROM wp_users": [
                {
                    "ID": 5,
                    "user_login": "alice",
                    "user_pass": "hash",
                    "user_nicename": "alice",
                    "user_email": "alice@example.com",
                    "user_url": "",
                    "user_registered": "2026-04-01 10:00:00",
                    "display_name": "Alice",
                }
            ],
            "FROM wp_usermeta": [
                {
                    "umeta_id": 1,
                    "user_id": 5,
                    "meta_key": "wp_capabilities",
                    "meta_value": 'a:1:{s:13:"administrator";b:1;}',
                },
                {
                    "umeta_id": 2,
                    "user_id": 5,
                    "meta_key": "zrz_weixin_uid",
                    "meta_value": "wx-user-1",
                },
                {
                    "umeta_id": 3,
                    "user_id": 5,
                    "meta_key": "zrz_open",
                    "meta_value": 'a:1:{s:17:"weixin_avatar_new";s:22:"https://img.test/a.jpg";}',
                },
            ],
        }
    )
    extractor = WordPressExtractor(client)
    payloads = extractor.extract_users()
    assert payloads[0]["role_label"] == "admin"
    assert payloads[0]["profile"]["avatar_url"] == "https://img.test/a.jpg"
    assert payloads[0]["social_identities"][0]["provider"] == "weixin"


def test_extract_users_preserves_numeric_avatar_without_using_it_as_url():
    client = FakeClient(
        {
            "FROM wp_users": [
                {
                    "ID": 5,
                    "user_login": "alice",
                    "user_pass": "hash",
                    "user_nicename": "alice",
                    "user_email": "alice@example.com",
                    "user_url": "",
                    "user_registered": "2026-04-01 10:00:00",
                    "display_name": "Alice",
                }
            ],
            "FROM wp_usermeta": [
                {
                    "umeta_id": 1,
                    "user_id": 5,
                    "meta_key": "zrz_open",
                    "meta_value": 'a:1:{s:6:"avatar";i:16151;}',
                },
            ],
        }
    )
    extractor = WordPressExtractor(client)
    payload = extractor.extract_users()[0]
    assert payload["profile"]["avatar_url"] is None
    assert payload["profile"]["meta_json"]["zrz_open"]["avatar"] == 16151


def test_map_wordpress_roles_defaults_to_guest():
    role_names, role_label = map_wordpress_roles({})
    assert role_names == []
    assert role_label == "guest"
