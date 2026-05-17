import pytest

from app.api.v1.auth import WordPressAuthClient


class FakeVerifiedUser:
    wordpress_user_id = 8
    login = "alice"
    email = "alice@example.com"
    display_name = "Alice"
    nicename = "alice"
    roles = ["administrator"]
    capabilities = {"administrator": True}


@pytest.mark.asyncio
async def test_login_session_logout_flow(client, monkeypatch):
    async def fake_verify_password(self, *, username_or_email: str, password: str):
        assert username_or_email == "alice"
        assert password == "secret"
        return FakeVerifiedUser()

    monkeypatch.setattr(WordPressAuthClient, "verify_password", fake_verify_password)

    login_response = await client.post(
        "/api/v1/auth/login",
        json={"username_or_email": "alice", "password": "secret"},
    )
    assert login_response.status_code == 200
    assert login_response.json()["user"]["username"] == "alice"
    assert "httponly" in login_response.headers["set-cookie"].lower()
    assert "samesite=lax" in login_response.headers["set-cookie"].lower()

    session_response = await client.get("/api/v1/auth/session")
    assert session_response.status_code == 200
    assert session_response.json()["role_names"] == ["administrator"]

    logout_response = await client.post("/api/v1/auth/logout")
    assert logout_response.status_code == 200

    session_after_logout = await client.get("/api/v1/auth/session")
    assert session_after_logout.status_code == 401


@pytest.mark.asyncio
async def test_register_and_reset_delegate_to_wordpress(client):
    register_response = await client.post(
        "/api/v1/auth/register",
        json={"username": "new-user", "email": "new@example.com"},
    )
    assert register_response.status_code == 202
    assert register_response.json()["status"] == "pending_wordpress"
    assert "wp-login.php?action=register" in register_response.json()["redirect_url"]

    reset_response = await client.post(
        "/api/v1/auth/reset-password",
        json={"email_or_username": "new@example.com"},
    )
    assert reset_response.status_code == 202
    assert "wp-login.php?action=lostpassword" in reset_response.json()["redirect_url"]
