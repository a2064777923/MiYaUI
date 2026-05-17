# Celery tasks module - placeholder for future task definitions
from celery import Celery

from app.config import settings

celery_app = Celery(
    "miyaui",
    broker=settings.REDIS_URL,
    backend=settings.REDIS_URL,
)

celery_app.autodiscover_tasks([])
