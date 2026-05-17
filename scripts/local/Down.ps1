$ErrorActionPreference = "Stop"

$composeArgs = @(
    "--env-file", ".env.local",
    "-f", "docker-compose.yml",
    "-f", "docker-compose.local.yml"
)

docker compose @composeArgs down
