from fastapi import APIRouter

from app.api.v1.auth import router as auth_router
from app.api.v1.content import router as content_router
from app.api.v1.health import router as health_router
from app.api.v1.migration import router as migration_router

api_router = APIRouter()
api_router.include_router(auth_router)
api_router.include_router(content_router)
api_router.include_router(health_router, tags=["health"])
api_router.include_router(migration_router)
