-- Initial PostgreSQL setup for MiyaUI
-- The main database is created by POSTGRES_DB env var in docker-compose.yml

CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";
