param(
    [string]$WordPressDump = "backups/server-dev-20260517/wordpress-dev.sql",
    [string]$PostgresDump = "backups/server-dev-20260517/refactor-postgres.sql"
)

$ErrorActionPreference = "Stop"

function Invoke-Compose {
    param(
        [Parameter(Mandatory = $true)]
        [string[]]$Arguments
    )

    & docker @Arguments
    if ($LASTEXITCODE -ne 0) {
        throw "docker $($Arguments -join ' ') failed with exit code $LASTEXITCODE"
    }
}

function Get-EnvValue {
    param([string]$Name)

    $line = Get-Content ".env.local" | Where-Object { $_ -match "^${Name}=" } | Select-Object -First 1
    if (-not $line) {
        throw "Missing ${Name} in .env.local"
    }
    return ($line -split "=", 2)[1]
}

function Escape-MySqlString {
    param([string]$Value)

    if ($null -eq $Value) {
        return ""
    }

    return $Value.Replace("'", "''")
}

$composeArgs = @(
    "--env-file", ".env.local",
    "-f", "docker-compose.yml",
    "-f", "docker-compose.local.yml"
)

$wpRootPassword = Get-EnvValue "WORDPRESS_DB_ROOT_PASSWORD"
$wpDbName = Get-EnvValue "WORDPRESS_DB_NAME"
$wpDbUser = Get-EnvValue "WORDPRESS_DB_USER"
$wpDbPassword = Get-EnvValue "WORDPRESS_DB_PASSWORD"
$wpHome = Get-EnvValue "WORDPRESS_HOME"
$wpSiteUrl = Get-EnvValue "WORDPRESS_SITEURL"

Invoke-Compose -Arguments (@("compose") + $composeArgs + @("up", "-d", "mysql", "postgres"))

if (Test-Path $WordPressDump) {
    $escapedDbUser = Escape-MySqlString $wpDbUser
    $escapedDbPassword = Escape-MySqlString $wpDbPassword
    $escapedWpHome = Escape-MySqlString $wpHome
    $escapedWpSiteUrl = Escape-MySqlString $wpSiteUrl
    $mysqlContainer = (& docker compose @composeArgs ps -q mysql).Trim()

    if (-not $mysqlContainer) {
        throw "MySQL container not found"
    }

    $bootstrapSql = @"
DROP DATABASE IF EXISTS $wpDbName;
CREATE DATABASE $wpDbName CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$escapedDbUser'@'%' IDENTIFIED BY '$escapedDbPassword';
GRANT ALL PRIVILEGES ON $wpDbName.* TO '$escapedDbUser'@'%';
FLUSH PRIVILEGES;
"@

    $bootstrapSql | & docker compose @composeArgs exec -T mysql mysql -uroot "-p$wpRootPassword"
    if ($LASTEXITCODE -ne 0) {
        throw "WordPress MySQL bootstrap failed with exit code $LASTEXITCODE"
    }

    & docker cp $WordPressDump "${mysqlContainer}:/tmp/wordpress-dev.sql"
    if ($LASTEXITCODE -ne 0) {
        throw "Copy WordPress dump into MySQL container failed with exit code $LASTEXITCODE"
    }

    Invoke-Compose -Arguments (@("compose") + $composeArgs + @("exec", "-T", "mysql", "sh", "-lc", "mysql --default-character-set=utf8mb4 -u$wpDbUser -p$wpDbPassword $wpDbName < /tmp/wordpress-dev.sql"))
    
    $wordpressUpdateSql = @"
UPDATE wp_options SET option_value='$escapedWpHome' WHERE option_name='home';
UPDATE wp_options SET option_value='$escapedWpSiteUrl' WHERE option_name='siteurl';
"@

    $wordpressUpdateSql | & docker compose @composeArgs exec -T mysql mysql "--default-character-set=utf8mb4" "-u$wpDbUser" "-p$wpDbPassword" $wpDbName
    if ($LASTEXITCODE -ne 0) {
        throw "WordPress site URL update failed with exit code $LASTEXITCODE"
    }
}

if (Test-Path $PostgresDump) {
    $postgresContainer = (& docker compose @composeArgs ps -q postgres).Trim()

    if (-not $postgresContainer) {
        throw "Postgres container not found"
    }

    Invoke-Compose -Arguments (@("compose") + $composeArgs + @("exec", "-T", "postgres", "psql", "-U", "miyaui", "-d", "postgres", "-c", "DROP DATABASE IF EXISTS miyaui;"))
    Invoke-Compose -Arguments (@("compose") + $composeArgs + @("exec", "-T", "postgres", "psql", "-U", "miyaui", "-d", "postgres", "-c", "CREATE DATABASE miyaui OWNER miyaui;"))

    & docker cp $PostgresDump "${postgresContainer}:/tmp/refactor-postgres.sql"
    if ($LASTEXITCODE -ne 0) {
        throw "Copy Postgres dump into container failed with exit code $LASTEXITCODE"
    }

    Invoke-Compose -Arguments (@("compose") + $composeArgs + @("exec", "-T", "postgres", "sh", "-lc", "psql -U miyaui -d miyaui -f /tmp/refactor-postgres.sql"))
}
