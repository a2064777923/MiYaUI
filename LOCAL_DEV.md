# 本地开发环境

## 目标

- `http://localhost:8082` 跑本地 WordPress dev 站
- `http://localhost:3001` 跑 Nuxt 重构前端
- `http://localhost:8001` 跑 FastAPI
- MySQL 5.7、PostgreSQL 16、Redis 都在 Docker 内，避免污染宿主机

## 文件

- `docker-compose.yml`：现有 refactor 服务
- `docker-compose.local.yml`：本地覆盖层，补上 WordPress/MySQL/本地挂载
- `.env.local`：本地端口和数据库凭据
- `scripts/local/Up.ps1`：启动本地容器
- `scripts/local/Down.ps1`：停止本地容器
- `scripts/local/Import-Dumps.ps1`：导入服务器拉下来的 WordPress/MySQL 与 refactor/Postgres 快照

## 启动

```powershell
./scripts/local/Up.ps1 -Build
```

首次拉起后导入数据：

```powershell
./scripts/local/Import-Dumps.ps1
```

停止：

```powershell
./scripts/local/Down.ps1
```

## 说明

- 本地 WordPress 使用 `docker/local/wordpress/wp-config.local.php`，不会直接使用服务器拉下来的 `wp-config.php`
- 本地 WordPress 已对齐服务器 dev 站的 `PHP 8.0 + SourceGuardian` loader，避免 `sg_load()` 缺失导致主题直接报错
- `wp-content/uploads/` 和站点源码直接挂载本地目录，便于你在宿主机改代码
- 当前导入脚本只会把 `home/siteurl` 改到本地地址，不会自动做全站序列化 search-replace
