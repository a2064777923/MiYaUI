from __future__ import annotations

from contextlib import AbstractContextManager
from dataclasses import dataclass
from typing import Any

import pymysql
from pymysql.connections import Connection
from pymysql.cursors import DictCursor

from app.core.wordpress import WordPressDatabaseConfig, get_wordpress_database_config


@dataclass(frozen=True)
class QuerySpec:
    sql: str
    params: tuple[Any, ...] = ()


class WordPressMySQLClient(AbstractContextManager["WordPressMySQLClient"]):
    def __init__(self, config: WordPressDatabaseConfig):
        self.config = config
        self.connection: Connection | None = None

    @classmethod
    def from_settings(cls) -> WordPressMySQLClient:
        return cls(get_wordpress_database_config())

    def connect(self) -> Connection:
        if self.connection is None:
            self.connection = pymysql.connect(
                host=self.config.host,
                port=self.config.port,
                user=self.config.username,
                password=self.config.password,
                database=self.config.database,
                charset="utf8mb4",
                cursorclass=DictCursor,
                autocommit=True,
            )
        return self.connection

    def close(self) -> None:
        if self.connection is not None:
            self.connection.close()
            self.connection = None

    def __exit__(self, exc_type, exc_val, exc_tb) -> None:
        self.close()

    def query(self, sql: str, params: tuple[Any, ...] | None = None) -> list[dict[str, Any]]:
        connection = self.connect()
        with connection.cursor() as cursor:
            cursor.execute(sql, params or ())
            rows = cursor.fetchall()
        return list(rows)

    def query_spec(self, spec: QuerySpec) -> list[dict[str, Any]]:
        return self.query(spec.sql, spec.params)

    def scalar(self, sql: str, params: tuple[Any, ...] | None = None) -> Any:
        rows = self.query(sql, params)
        if not rows:
            return None
        return next(iter(rows[0].values()))

    def count(self, table_name: str, where_sql: str = "", params: tuple[Any, ...] | None = None) -> int:
        sql = f"SELECT COUNT(*) AS c FROM {table_name}"
        if where_sql:
            sql += f" WHERE {where_sql}"
        value = self.scalar(sql, params)
        return int(value or 0)


def prefixed_table(prefix: str, table_name: str) -> str:
    return f"{prefix}{table_name}"
