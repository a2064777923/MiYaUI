param(
    [switch]$Build
)

$ErrorActionPreference = "Stop"

$composeArgs = @(
    "--env-file", ".env.local",
    "-f", "docker-compose.yml",
    "-f", "docker-compose.local.yml"
)

$upArgs = @("compose") + $composeArgs + @("up", "-d")
if ($Build) {
    $upArgs += "--build"
}

docker @upArgs
