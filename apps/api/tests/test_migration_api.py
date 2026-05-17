from app.models.migration import MigrationRun


async def test_migration_schema_endpoint_lists_registered_tables(client):
    response = await client.get("/api/v1/migration/schema")
    assert response.status_code == 200
    payload = response.json()
    assert "content_items" in payload["tables"]
    assert payload["count"] >= 10


async def test_migration_runs_endpoint_handles_empty_state(client):
    response = await client.get("/api/v1/migration/runs")
    assert response.status_code == 200
    assert response.json() == []


async def test_migration_runs_endpoint_returns_latest_runs(client, db_session):
    run = MigrationRun(dataset="content", mode="backfill", status="completed", dry_run=False)
    db_session.add(run)
    await db_session.commit()
    await db_session.refresh(run)

    response = await client.get("/api/v1/migration/runs")
    assert response.status_code == 200
    payload = response.json()
    assert payload[0]["dataset"] == "content"
    assert payload[0]["status"] == "completed"
