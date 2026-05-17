from __future__ import annotations

from collections import defaultdict
from datetime import datetime
from typing import Any

from app.core.php_serialization import normalize_meta_value
from app.migration.mysql import QuerySpec, WordPressMySQLClient, prefixed_table
from app.services.content import (
    build_content_excerpt,
    build_content_search_text,
    build_content_seo,
    build_original_url_path,
)

PUBLIC_CONTENT_TYPES = (
    "post",
    "page",
    "announcement",
    "circle",
    "document",
    "newsflashes",
    "shop",
    "links",
    "infomation",
    "cpay",
    "ask",
    "answer",
    "question",
    "site",
    "gonggao",
    "thread",
    "forum",
)
SOCIAL_PROVIDER_META_KEYS = {
    "qq": ("zrz_qq_uid",),
    "weixin": ("zrz_weixin_uid", "zrz_weixin_open_id", "open_mpweixin_openid"),
    "weibo": ("zrz_weibo_uid",),
    "baidu": ("zrz_baidu_uid", "zrz_juhebaidu_uid"),
    "google": ("zrz_google_uid",),
}
DATASET_TABLES = {
    "content": "posts",
    "users": "users",
    "community": "comments",
    "commerce-legacy": "zrz_order",
    "media": "posts",
}


def parse_wp_datetime(value: str | bytes | datetime | None) -> datetime | None:
    if not value or value == "0000-00-00 00:00:00":
        return None
    if isinstance(value, datetime):
        return value
    if isinstance(value, bytes):
        value = value.decode("utf-8")
    return datetime.strptime(value, "%Y-%m-%d %H:%M:%S")


def list_to_sql_params(values: list[Any]) -> tuple[str, tuple[Any, ...]]:
    placeholders = ", ".join(["%s"] * len(values))
    return placeholders, tuple(values)


def map_wordpress_roles(capabilities: dict[str, Any]) -> tuple[list[str], str]:
    role_names = [role for role, enabled in capabilities.items() if enabled]
    if "administrator" in role_names:
        return role_names, "admin"
    if "editor" in role_names:
        return role_names, "editor"
    if role_names:
        return role_names, "member"
    return role_names, "guest"


def string_or_none(value: Any) -> str | None:
    if isinstance(value, str):
        stripped = value.strip()
        return stripped or None
    return None


def url_or_none(value: Any) -> str | None:
    candidate = string_or_none(value)
    if not candidate:
        return None
    if candidate.startswith(("http://", "https://", "//", "/")):
        return candidate
    return None


class WordPressExtractor:
    def __init__(self, client: WordPressMySQLClient):
        self.client = client
        self.prefix = client.config.table_prefix

    def build_posts_query(
        self,
        *,
        post_types: tuple[str, ...] = PUBLIC_CONTENT_TYPES,
        since_id: int | None = None,
        limit: int | None = None,
        include_statuses: tuple[str, ...] = ("publish",),
    ) -> QuerySpec:
        posts_table = prefixed_table(self.prefix, "posts")
        type_sql, type_params = list_to_sql_params(list(post_types))
        status_sql, status_params = list_to_sql_params(list(include_statuses))
        sql = (
            f"SELECT ID, post_author, post_date, post_modified, post_content, post_title, post_excerpt, "
            f"post_status, post_name, post_parent, guid, menu_order, post_type, post_mime_type, comment_count "
            f"FROM {posts_table} WHERE post_type IN ({type_sql}) AND post_status IN ({status_sql})"
        )
        params: list[Any] = [*type_params, *status_params]
        if since_id is not None:
            sql += " AND ID > %s"
            params.append(since_id)
        sql += " ORDER BY ID ASC"
        if limit is not None:
            sql += " LIMIT %s"
            params.append(limit)
        return QuerySpec(sql, tuple(params))

    def build_postmeta_query(self, post_ids: list[int]) -> QuerySpec:
        table = prefixed_table(self.prefix, "postmeta")
        placeholders, params = list_to_sql_params(post_ids)
        sql = (
            f"SELECT meta_id, post_id, meta_key, meta_value "
            f"FROM {table} WHERE post_id IN ({placeholders}) ORDER BY meta_id ASC"
        )
        return QuerySpec(
            sql,
            params,
        )

    def build_taxonomy_relationship_query(self, post_ids: list[int]) -> QuerySpec:
        relationships = prefixed_table(self.prefix, "term_relationships")
        taxonomies = prefixed_table(self.prefix, "term_taxonomy")
        terms = prefixed_table(self.prefix, "terms")
        placeholders, params = list_to_sql_params(post_ids)
        sql = (
            f"SELECT tr.object_id, tr.term_taxonomy_id, tt.term_id, tt.taxonomy, tt.description, tt.parent, tt.count, "
            f"t.name, t.slug FROM {relationships} tr "
            f"JOIN {taxonomies} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id "
            f"JOIN {terms} t ON t.term_id = tt.term_id "
            f"WHERE tr.object_id IN ({placeholders}) ORDER BY tr.object_id ASC"
        )
        return QuerySpec(sql, params)

    def build_users_query(self, *, since_id: int | None = None, limit: int | None = None) -> QuerySpec:
        table = prefixed_table(self.prefix, "users")
        sql = (
            f"SELECT ID, user_login, user_pass, user_nicename, user_email, user_url, user_registered, display_name "
            f"FROM {table} WHERE 1=1"
        )
        params: list[Any] = []
        if since_id is not None:
            sql += " AND ID > %s"
            params.append(since_id)
        sql += " ORDER BY ID ASC"
        if limit is not None:
            sql += " LIMIT %s"
            params.append(limit)
        return QuerySpec(sql, tuple(params))

    def build_usermeta_query(self, user_ids: list[int]) -> QuerySpec:
        table = prefixed_table(self.prefix, "usermeta")
        placeholders, params = list_to_sql_params(user_ids)
        sql = (
            f"SELECT umeta_id, user_id, meta_key, meta_value "
            f"FROM {table} WHERE user_id IN ({placeholders}) ORDER BY umeta_id ASC"
        )
        return QuerySpec(
            sql,
            params,
        )

    def build_comments_query(self, *, since_id: int | None = None, limit: int | None = None) -> QuerySpec:
        table = prefixed_table(self.prefix, "comments")
        sql = (
            f"SELECT comment_ID, comment_post_ID, comment_author, comment_author_email, comment_date, "
            f"comment_content, comment_approved, comment_type, comment_parent, user_id "
            f"FROM {table} WHERE comment_type='comment'"
        )
        params: list[Any] = []
        if since_id is not None:
            sql += " AND comment_ID > %s"
            params.append(since_id)
        sql += " ORDER BY comment_ID ASC"
        if limit is not None:
            sql += " LIMIT %s"
            params.append(limit)
        return QuerySpec(sql, tuple(params))

    def build_direct_messages_query(self, *, since_id: int | None = None, limit: int | None = None) -> QuerySpec:
        table = prefixed_table(self.prefix, "zrz_directmessage")
        sql = f"SELECT id, mark, `from`, `to`, date, status, content, `key`, value FROM {table} WHERE 1=1"
        params: list[Any] = []
        if since_id is not None:
            sql += " AND id > %s"
            params.append(since_id)
        sql += " ORDER BY id ASC"
        if limit is not None:
            sql += " LIMIT %s"
            params.append(limit)
        return QuerySpec(sql, tuple(params))

    def build_orders_query(self, *, since_id: int | None = None, limit: int | None = None) -> QuerySpec:
        table = prefixed_table(self.prefix, "zrz_order")
        sql = (
            f"SELECT id, order_id, user_id, post_id, order_type, order_commodity, order_state, order_date, "
            f"order_count, order_price, order_total, money_type, order_key, order_value, order_content, "
            f"pay_type, tracking_number, order_address, order_mark, order_mobile FROM {table} WHERE 1=1"
        )
        params: list[Any] = []
        if since_id is not None:
            sql += " AND id > %s"
            params.append(since_id)
        sql += " ORDER BY id ASC"
        if limit is not None:
            sql += " LIMIT %s"
            params.append(limit)
        return QuerySpec(sql, tuple(params))

    def build_gold_query(self, *, since_id: int | None = None, limit: int | None = None) -> QuerySpec:
        table = prefixed_table(self.prefix, "b2_gold")
        sql = (
            f"SELECT id, date, `from`, `to`, gold_type, `no`, total, type, type_text, "
            f"post_id, `count`, `read`, msg, `key`, value "
            f"FROM {table} WHERE 1=1"
        )
        params: list[Any] = []
        if since_id is not None:
            sql += " AND id > %s"
            params.append(since_id)
        sql += " ORDER BY id ASC"
        if limit is not None:
            sql += " LIMIT %s"
            params.append(limit)
        return QuerySpec(sql, tuple(params))

    def build_cards_query(self, *, since_id: int | None = None, limit: int | None = None) -> QuerySpec:
        table = prefixed_table(self.prefix, "zrz_card")
        sql = f"SELECT id, card_key, card_value, card_rmb, card_status, card_user FROM {table} WHERE 1=1"
        params: list[Any] = []
        if since_id is not None:
            sql += " AND id > %s"
            params.append(since_id)
        sql += " ORDER BY id ASC"
        if limit is not None:
            sql += " LIMIT %s"
            params.append(limit)
        return QuerySpec(sql, tuple(params))

    def build_attachments_query(self, *, since_id: int | None = None, limit: int | None = None) -> QuerySpec:
        return self.build_posts_query(
            post_types=("attachment",),
            since_id=since_id,
            limit=limit,
            include_statuses=("inherit", "publish"),
        )

    def _meta_map(self, meta_rows: list[dict[str, Any]], id_key: str) -> dict[int, list[dict[str, Any]]]:
        grouped: dict[int, list[dict[str, Any]]] = defaultdict(list)
        for row in meta_rows:
            grouped[int(row[id_key])].append(row)
        return grouped

    def _taxonomy_map(self, rows: list[dict[str, Any]]) -> dict[int, list[dict[str, Any]]]:
        grouped: dict[int, list[dict[str, Any]]] = defaultdict(list)
        for row in rows:
            grouped[int(row["object_id"])].append(row)
        return grouped

    def extract_content(self, *, since_id: int | None = None, limit: int | None = None) -> list[dict[str, Any]]:
        posts = self.client.query_spec(self.build_posts_query(since_id=since_id, limit=limit))
        if not posts:
            return []
        post_ids = [int(row["ID"]) for row in posts]
        meta_rows = self.client.query_spec(self.build_postmeta_query(post_ids))
        taxonomy_rows = self.client.query_spec(self.build_taxonomy_relationship_query(post_ids))
        meta_map = self._meta_map(meta_rows, "post_id")
        taxonomy_map = self._taxonomy_map(taxonomy_rows)
        payloads: list[dict[str, Any]] = []
        for row in posts:
            post_id = int(row["ID"])
            metas = []
            meta_json: dict[str, Any] = {}
            for meta in meta_map.get(post_id, []):
                normalized = normalize_meta_value(meta.get("meta_value"))
                metas.append(
                    {
                        "meta_key": meta["meta_key"],
                        "raw_value": normalized["raw"],
                        "parsed_value": normalized,
                        "source_table": prefixed_table(self.prefix, "postmeta"),
                        "source_id": str(meta["meta_id"]),
                        "source_updated_at": parse_wp_datetime(row.get("post_modified")),
                    }
                )
                meta_json[str(meta["meta_key"])] = normalized["value"]
            terms = []
            for item in taxonomy_map.get(post_id, []):
                terms.append(
                    {
                        "source_table": prefixed_table(self.prefix, "term_relationships"),
                        "source_id": f"{item['object_id']}:{item['term_taxonomy_id']}",
                        "source_updated_at": parse_wp_datetime(row.get("post_modified")),
                        "taxonomy": {
                            "source_system": "wordpress",
                            "source_table": prefixed_table(self.prefix, "term_taxonomy"),
                            "source_id": str(item["term_taxonomy_id"]),
                            "source_updated_at": parse_wp_datetime(row.get("post_modified")),
                            "taxonomy": item["taxonomy"],
                            "description": item.get("description") or "",
                            "parent_source_id": str(item["parent"]) if item.get("parent") else None,
                            "count": int(item.get("count") or 0),
                        },
                        "term": {
                            "source_system": "wordpress",
                            "source_table": prefixed_table(self.prefix, "terms"),
                            "source_id": str(item["term_id"]),
                            "source_updated_at": parse_wp_datetime(row.get("post_modified")),
                            "slug": item["slug"],
                            "name": item["name"],
                            "description": item.get("description") or "",
                        },
                    }
                )
            original_url_path = build_original_url_path(post_id)
            seo_json = build_content_seo(meta_json, row["post_title"], row["post_excerpt"], original_url_path)
            payloads.append(
                {
                    "source_system": "wordpress",
                    "source_table": prefixed_table(self.prefix, "posts"),
                    "source_id": str(post_id),
                    "source_updated_at": parse_wp_datetime(row.get("post_modified")),
                    "wordpress_post_id": post_id,
                    "author_source_id": str(row["post_author"]),
                    "content_type": row["post_type"],
                    "status": row["post_status"],
                    "slug": row.get("post_name") or "",
                    "title": row.get("post_title") or "",
                    "body": row.get("post_content") or "",
                    "excerpt": build_content_excerpt(row.get("post_content") or "", row.get("post_excerpt") or ""),
                    "published_at": parse_wp_datetime(row.get("post_date")),
                    "content_updated_at": parse_wp_datetime(row.get("post_modified")),
                    "original_url_path": original_url_path,
                    "seo_title": seo_json["title"],
                    "seo_description": seo_json["description"],
                    "seo_json": seo_json,
                    "meta_json": meta_json,
                    "search_text": build_content_search_text(
                        row.get("post_title") or "",
                        row.get("post_excerpt") or "",
                        row.get("post_content") or "",
                    ),
                    "render_mode": "ssr",
                    "fallback_required": False,
                    "metas": metas,
                    "terms": terms,
                }
            )
        return payloads

    def extract_users(self, *, since_id: int | None = None, limit: int | None = None) -> list[dict[str, Any]]:
        users = self.client.query_spec(self.build_users_query(since_id=since_id, limit=limit))
        if not users:
            return []
        user_ids = [int(row["ID"]) for row in users]
        meta_rows = self.client.query_spec(self.build_usermeta_query(user_ids))
        meta_map = self._meta_map(meta_rows, "user_id")
        payloads: list[dict[str, Any]] = []
        for row in users:
            user_id = int(row["ID"])
            meta_json: dict[str, Any] = {}
            for meta in meta_map.get(user_id, []):
                meta_json[str(meta["meta_key"])] = normalize_meta_value(meta.get("meta_value"))["value"]
            capabilities = meta_json.get("wp_capabilities") or {}
            role_names, role_label = map_wordpress_roles(capabilities if isinstance(capabilities, dict) else {})
            avatar_url = None
            open_meta = meta_json.get("zrz_open")
            if isinstance(open_meta, dict):
                avatar_url = url_or_none(open_meta.get("weixin_avatar_new")) or url_or_none(open_meta.get("avatar"))
            roles = [
                {
                    "role_name": role_name,
                    "capability_json": capabilities,
                    "source_id": f"{user_id}:{role_name}",
                    "source_updated_at": parse_wp_datetime(row.get("user_registered")),
                }
                for role_name in role_names
            ]
            identities = []
            for provider, keys in SOCIAL_PROVIDER_META_KEYS.items():
                for key in keys:
                    value = meta_json.get(key)
                    if not value:
                        continue
                    identities.append(
                        {
                            "provider": provider if provider != "weixin" or key != "open_mpweixin_openid" else "wechat",
                            "provider_user_id": str(value),
                            "email": row.get("user_email") or None,
                            "unionid": (
                                str(meta_json.get("zrz_weixin_unionid"))
                                if meta_json.get("zrz_weixin_unionid")
                                else None
                            ),
                            "openid": str(value) if "openid" in key else None,
                            "profile_json": open_meta if isinstance(open_meta, dict) else {},
                            "source_id": f"{user_id}:{provider}:{key}",
                            "source_updated_at": parse_wp_datetime(row.get("user_registered")),
                        }
                    )
            payloads.append(
                {
                    "source_system": "wordpress",
                    "source_table": prefixed_table(self.prefix, "users"),
                    "source_id": str(user_id),
                    "source_updated_at": parse_wp_datetime(row.get("user_registered")),
                    "wordpress_user_id": user_id,
                    "username": row["user_login"],
                    "email": row.get("user_email") or f"user{user_id}@local.invalid",
                    "nicename": row.get("user_nicename") or None,
                    "display_name": row.get("display_name") or row["user_login"],
                    "role_label": role_label,
                    "password_authority": "wordpress",
                    "first_relogin_required": True,
                    "meta_json": meta_json,
                    "profile": {
                        "avatar_url": avatar_url,
                        "phone": string_or_none(
                            meta_json.get("open_phone") or meta_json.get("mobile") or meta_json.get("weixinhaoma")
                        ),
                        "bio": string_or_none(meta_json.get("description")) or "",
                        "meta_json": meta_json,
                    },
                    "roles": roles,
                    "social_identities": identities,
                }
            )
        return payloads

    def extract_comments(self, *, since_id: int | None = None, limit: int | None = None) -> list[dict[str, Any]]:
        rows = self.client.query_spec(self.build_comments_query(since_id=since_id, limit=limit))
        payloads = []
        for row in rows:
            payloads.append(
                {
                    "source_system": "wordpress",
                    "source_table": prefixed_table(self.prefix, "comments"),
                    "source_id": str(row["comment_ID"]),
                    "source_updated_at": parse_wp_datetime(row.get("comment_date")),
                    "content_item_source_id": str(row["comment_post_ID"]),
                    "author_source_user_id": str(row["user_id"]) if row.get("user_id") else None,
                    "author_name": row.get("comment_author") or "",
                    "author_email": row.get("comment_author_email") or None,
                    "parent_source_id": str(row["comment_parent"]) if row.get("comment_parent") else None,
                    "status": str(row.get("comment_approved") or "1"),
                    "body": row.get("comment_content") or "",
                }
            )
        return payloads

    def extract_circle_topics(self, *, since_id: int | None = None, limit: int | None = None) -> list[dict[str, Any]]:
        posts = self.client.query_spec(
            self.build_posts_query(
                post_types=("circle",),
                since_id=since_id,
                limit=limit,
                include_statuses=("publish",),
            )
        )
        return [
            {
                "source_system": "wordpress",
                "source_table": prefixed_table(self.prefix, "posts"),
                "source_id": str(row["ID"]),
                "source_updated_at": parse_wp_datetime(row.get("post_modified")),
                "circle_source_id": str(row["post_parent"]) if row.get("post_parent") else str(row["ID"]),
                "author_source_user_id": str(row["post_author"]),
                "title": row.get("post_title") or "",
                "body": row.get("post_content") or "",
                "status": row.get("post_status") or "publish",
                "meta_json": {},
            }
            for row in posts
        ]

    def extract_question_answers(
        self,
        *,
        since_id: int | None = None,
        limit: int | None = None,
    ) -> list[dict[str, Any]]:
        posts = self.client.query_spec(
            self.build_posts_query(
                post_types=("ask", "answer", "question"),
                since_id=since_id,
                limit=limit,
                include_statuses=("publish",),
            )
        )
        payloads = []
        for row in posts:
            entry_type = "answer" if row["post_type"] == "answer" else "question"
            payloads.append(
                {
                    "source_system": "wordpress",
                    "source_table": prefixed_table(self.prefix, "posts"),
                    "source_id": str(row["ID"]),
                    "source_updated_at": parse_wp_datetime(row.get("post_modified")),
                    "content_item_source_id": str(row["ID"]),
                    "entry_type": entry_type,
                    "question_source_id": str(row["post_parent"] or row["ID"]),
                    "answer_source_id": str(row["ID"]) if entry_type == "answer" else None,
                    "author_source_user_id": str(row["post_author"]),
                    "accepted": False,
                    "body": row.get("post_content") or "",
                    "meta_json": {},
                }
            )
        return payloads

    def extract_direct_messages(self, *, since_id: int | None = None, limit: int | None = None) -> list[dict[str, Any]]:
        rows = self.client.query_spec(self.build_direct_messages_query(since_id=since_id, limit=limit))
        return [
            {
                "source_system": "wordpress",
                "source_table": prefixed_table(self.prefix, "zrz_directmessage"),
                "source_id": str(row["id"]),
                "source_updated_at": parse_wp_datetime(row.get("date")),
                "sender_source_user_id": str(row["from"]),
                "receiver_source_user_id": str(row["to"]),
                "conversation_key": row.get("mark") or f"{row['from']}:{row['to']}",
                "body": row.get("content") or "",
                "read_at": parse_wp_datetime(row.get("date")).isoformat() if int(row.get("status") or 0) else None,
            }
            for row in rows
        ]

    def extract_legacy_orders(self, *, since_id: int | None = None, limit: int | None = None) -> list[dict[str, Any]]:
        rows = self.client.query_spec(self.build_orders_query(since_id=since_id, limit=limit))
        return [
            {
                "source_system": "wordpress",
                "source_table": prefixed_table(self.prefix, "zrz_order"),
                "source_id": str(row["id"]),
                "source_updated_at": parse_wp_datetime(row.get("order_date")),
                "order_no": row.get("order_id"),
                "user_source_id": str(row["user_id"]) if row.get("user_id") is not None else None,
                "amount": float(row.get("order_total") or 0),
                "currency": "CNY",
                "status": row.get("order_state") or "pending",
                "payment_gateway": row.get("pay_type"),
                "paid_at": parse_wp_datetime(row.get("order_date")),
                "authoritative_system": "wordpress",
                "is_read_only": True,
                "meta_json": {key: row.get(key) for key in ("order_type", "order_content", "order_key", "order_value")},
            }
            for row in rows
        ]

    def extract_legacy_gold_transactions(
        self,
        *,
        since_id: int | None = None,
        limit: int | None = None,
    ) -> list[dict[str, Any]]:
        rows = self.client.query_spec(self.build_gold_query(since_id=since_id, limit=limit))
        return [
            {
                "source_system": "wordpress",
                "source_table": prefixed_table(self.prefix, "b2_gold"),
                "source_id": str(row["id"]),
                "source_updated_at": parse_wp_datetime(row.get("date")),
                "user_source_id": str(row["to"]) if row.get("to") is not None else None,
                "amount": float(row.get("no") or 0),
                "balance_after": float(row.get("total") or 0),
                "reason": row.get("type"),
                "note": row.get("msg") or "",
                "is_read_only": True,
            }
            for row in rows
        ]

    def extract_legacy_vip_memberships(
        self,
        *,
        since_id: int | None = None,
        limit: int | None = None,
    ) -> list[dict[str, Any]]:
        rows = self.client.query_spec(self.build_cards_query(since_id=since_id, limit=limit))
        return [
            {
                "source_system": "wordpress",
                "source_table": prefixed_table(self.prefix, "zrz_card"),
                "source_id": str(row["id"]),
                "source_updated_at": None,
                "user_source_id": str(row["card_user"]) if row.get("card_user") is not None else None,
                "plan_name": row.get("card_key"),
                "status": "active" if int(row.get("card_status") or 0) == 1 else "inactive",
                "starts_at": None,
                "ends_at": None,
                "is_read_only": True,
                "meta_json": {"card_value": row.get("card_value"), "card_rmb": row.get("card_rmb")},
            }
            for row in rows
        ]

    def extract_media(self, *, since_id: int | None = None, limit: int | None = None) -> list[dict[str, Any]]:
        attachments = self.client.query_spec(self.build_attachments_query(since_id=since_id, limit=limit))
        if not attachments:
            return []
        attachment_ids = [int(row["ID"]) for row in attachments]
        meta_rows = self.client.query_spec(self.build_postmeta_query(attachment_ids))
        meta_map = self._meta_map(meta_rows, "post_id")
        payloads: list[dict[str, Any]] = []
        for row in attachments:
            attachment_id = int(row["ID"])
            meta_json: dict[str, Any] = {}
            for meta in meta_map.get(attachment_id, []):
                meta_json[str(meta["meta_key"])] = normalize_meta_value(meta.get("meta_value"))["value"]
            attached_file = meta_json.get("_wp_attached_file")
            metadata = meta_json.get("_wp_attachment_metadata") or {}
            if isinstance(metadata, dict):
                width = metadata.get("width")
                height = metadata.get("height")
            else:
                width = None
                height = None
            payloads.append(
                {
                    "source_system": "wordpress",
                    "source_table": prefixed_table(self.prefix, "posts"),
                    "source_id": str(attachment_id),
                    "source_updated_at": parse_wp_datetime(row.get("post_modified")),
                    "attachment_source_id": str(attachment_id),
                    "content_item_source_id": str(row["post_parent"]) if row.get("post_parent") else None,
                    "source_url": row.get("guid") or "",
                    "source_path": attached_file,
                    "mime_type": row.get("post_mime_type"),
                    "title": row.get("post_title") or "",
                    "alt_text": meta_json.get("_wp_attachment_image_alt"),
                    "width": int(width) if width else None,
                    "height": int(height) if height else None,
                    "metadata_json": meta_json,
                }
            )
        return payloads

    def source_count_for_dataset(self, dataset: str) -> int:
        if dataset == "content":
            type_sql, type_params = list_to_sql_params(list(PUBLIC_CONTENT_TYPES))
            return self.client.count(
                prefixed_table(self.prefix, "posts"),
                f"post_type IN ({type_sql}) AND post_status='publish'",
                type_params,
            )
        if dataset == "users":
            return self.client.count(prefixed_table(self.prefix, "users"))
        if dataset == "community":
            comments = self.client.count(prefixed_table(self.prefix, "comments"), "comment_type='comment'")
            messages = self.client.count(prefixed_table(self.prefix, "zrz_directmessage"))
            circles = self.client.count(
                prefixed_table(self.prefix, "posts"),
                "post_type='circle' AND post_status='publish'",
            )
            qa_type_sql, qa_type_params = list_to_sql_params(["ask", "answer", "question"])
            qa = self.client.count(
                prefixed_table(self.prefix, "posts"),
                f"post_type IN ({qa_type_sql}) AND post_status='publish'",
                qa_type_params,
            )
            return comments + messages + circles + qa
        if dataset == "commerce-legacy":
            return self.client.count(prefixed_table(self.prefix, "zrz_order")) + self.client.count(
                prefixed_table(self.prefix, "b2_gold")
            ) + self.client.count(prefixed_table(self.prefix, "zrz_card"))
        if dataset == "media":
            return self.client.count(prefixed_table(self.prefix, "posts"), "post_type='attachment'")
        raise ValueError(f"Unsupported dataset: {dataset}")

    def extract_dataset(
        self,
        dataset: str,
        *,
        since_id: int | None = None,
        limit: int | None = None,
    ) -> list[dict[str, Any]]:
        if dataset == "content":
            return self.extract_content(since_id=since_id, limit=limit)
        if dataset == "users":
            return self.extract_users(since_id=since_id, limit=limit)
        if dataset == "community":
            return [
                *self.extract_comments(since_id=since_id, limit=limit),
                *self.extract_direct_messages(since_id=since_id, limit=limit),
                *self.extract_circle_topics(since_id=since_id, limit=limit),
                *self.extract_question_answers(since_id=since_id, limit=limit),
            ]
        if dataset == "commerce-legacy":
            return [
                *self.extract_legacy_orders(since_id=since_id, limit=limit),
                *self.extract_legacy_gold_transactions(since_id=since_id, limit=limit),
                *self.extract_legacy_vip_memberships(since_id=since_id, limit=limit),
            ]
        if dataset == "media":
            return self.extract_media(since_id=since_id, limit=limit)
        raise ValueError(f"Unsupported dataset: {dataset}")
