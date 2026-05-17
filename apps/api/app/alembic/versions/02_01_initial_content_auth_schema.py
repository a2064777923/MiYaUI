from __future__ import annotations

from alembic import op
from sqlmodel import SQLModel

from app.models import PHASE_02_MODELS  # noqa: F401

# revision identifiers, used by Alembic.
revision = "0201_init_auth_content"
down_revision = None
branch_labels = None
depends_on = None


def upgrade() -> None:
    bind = op.get_bind()
    SQLModel.metadata.create_all(bind=bind)


def downgrade() -> None:
    bind = op.get_bind()
    SQLModel.metadata.drop_all(bind=bind)
