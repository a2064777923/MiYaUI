from __future__ import annotations

from alembic import op

# revision identifiers, used by Alembic.
revision = "0202_allow_duplicate_term_slugs"
down_revision = "0201_init_auth_content"
branch_labels = None
depends_on = None


def upgrade() -> None:
    op.drop_constraint("uq_terms_slug", "terms", type_="unique")


def downgrade() -> None:
    op.create_unique_constraint("uq_terms_slug", "terms", ["slug"])
