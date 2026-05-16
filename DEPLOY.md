# MiYaUI (蜜芽设计) 网站部署文档

## 项目概述

蜜芽设计 (www.miyaui.com) 是一个基于 WordPress 的设计资源分享平台，使用 B2 商业主题 + Jitheme 子主题。

---

## 技术栈

| 组件 | 版本 | 用途 |
|------|------|------|
| WordPress | 6.9.4 | CMS 核心 |
| PHP | 8.0.26 | 后端语言 |
| MySQL | 5.7.44 | 数据库 |
| Nginx | 1.28.3 | Web 服务器 |
| Redis | - | 对象缓存 |
| B2 Theme | 5.9.42 | 商业父主题 |
| Jitheme | 2.9.0 | 子主题 |
| Swoole Loader | 3.2.1 | B2 主题必需的 PHP 扩展 |

---

## 服务器架构

```
用户请求
    │
    ▼
Nginx (端口 80/443)  ← SSL 终止 + 反向代理
    │
    ▼
Nginx (端口 8081)    ← 后端服务
    │
    ▼
PHP-FPM 8.0 (Unix Socket: /tmp/php-cgi-80.sock)
    │
    ▼
WordPress + MySQL + Redis
```

---

## 部署步骤

### 一、系统环境准备

#### 1.1 安装宝塔面板 (推荐)

```bash
# CentOS / OpenCloudOS / RHEL
yum install -y wget && wget -O install.sh https://download.bt.cn/install/install_6.0.sh && sh install.sh

# Ubuntu / Debian
wget -O install.sh https://download.bt.cn/install/install-ubuntu_6.0.sh && sudo bash install.sh
```

安装完成后登录面板，安装以下软件：
- **Nginx 1.28+**
- **PHP 8.0** (编译安装，勾选 swoole 扩展)
- **MySQL 5.7**
- **Redis 7.x**
- **phpMyAdmin** (可选，方便数据库管理)

#### 1.2 PHP 8.0 扩展要求

PHP 8.0 必须安装以下扩展（宝塔面板 → 软件商店 → PHP 8.0 → 安装扩展）：

| 扩展 | 是否必需 | 说明 |
|------|----------|------|
| swoole_loader | **必需** | B2 主题依赖，版本必须为 3.2.1 |
| redis | **必需** | Redis 对象缓存 |
| mysqli | **必需** | 数据库连接 |
| gd / imagick | 推荐 | 图片处理 |
| curl | 推荐 | HTTP 请求 |
| zip | 推荐 | 插件/主题更新 |
| mbstring | 推荐 | 多字节字符串 |
| intl | 推荐 | 国际化支持 |
| bcmath | 推荐 | 高精度计算 |
| soap | 推荐 | SOAP 接口 |

#### 1.3 Swoole Loader 安装

B2 主题自带 swoole_loader 扩展文件，位于：

```
wp-content/themes/b2/Assets/admin/loader/loader80.so
```

在 `php.ini` 中添加：

```ini
[swoole_loader]
extension=/www/wwwroot/www.miyaui.com/wp-content/themes/b2/Assets/admin/loader/loader80.so
```

> 根据 PHP 版本选择对应的 .so 文件：loader74.so / loader80.so / loader82.so 等

---

### 二、创建站点

#### 2.1 宝塔面板创建站点

1. **宝塔面板** → **网站** → **添加站点**
2. 填写：
   - 域名：`www.miyaui.com`
   - PHP 版本：`PHP-80`
   - 数据库：`MySQL`，数据库名 `www_miyaui_com`，用户名 `www_miyaui_com`，设置密码
3. 点击提交

#### 2.2 配置 SSL 证书

1. 宝塔面板 → 网站 → 设置 → SSL
2. 选择「其他证书」，粘贴：
   - **证书(PEM格式)**: `fullchain.pem` 的内容
   - **密钥(KEY)**: `privkey.pem` 的内容
3. 强制 HTTPS：开启

证书文件位置（从原服务器拷贝）：
```
/www/server/panel/vhost/cert/www.miyaui.com/fullchain.pem
/www/server/panel/vhost/cert/www.miyaui.com/privkey.pem
```

---

### 三、部署网站文件

#### 3.1 上传网站文件

将网站根目录所有文件上传到新服务器的站点根目录：

```bash
# 网站根目录
/www/wwwroot/www.miyaui.com/
```

**需要上传的核心目录和文件：**

```
/www/wwwroot/www.miyaui.com/
├── wp-admin/              # WordPress 后台核心
├── wp-includes/           # WordPress 核心函数
├── wp-content/            # 所有内容（最重要！）
│   ├── themes/            # 主题文件
│   │   ├── b2/            # B2 父主题 (5.9.42)
│   │   └── b2Jitheme/     # Jitheme 子主题 (2.9.0)
│   ├── plugins/           # 插件
│   ├── uploads/           # 上传的媒体文件 (~9.2GB)
│   ├── mu-plugins/        # 必须加载的插件 (Redis object-cache.php)
│   └── object-cache.php   # Redis 对象缓存 drop-in
├── wp-config.php          # WordPress 配置文件
├── index.php              # 入口文件
├── wp-login.php           # 登录页
├── wp-cron.php            # 定时任务
├── wp-settings.php        # 核心设置
├── .htaccess              # Apache 重写规则 (Nginx 环境可忽略)
└── .user.ini              # PHP 配置
```

**文件权限设置：**

```bash
chown -R www:www /www/wwwroot/www.miyaui.com/
chmod 755 /www/wwwroot/www.miyaui.com/
chmod 644 /www/wwwroot/www.miyaui.com/wp-config.php
chmod 644 /www/wwwroot/www.miyaui.com/.htaccess
```

#### 3.2 修改 wp-config.php

```php
<?php
// 处理反向代理 HTTPS（如果使用反向代理架构）
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// ** 数据库设置 ** //
define( 'DB_NAME', '你的数据库名' );          // 改为新数据库名
define( 'DB_USER', '你的数据库用户名' );      // 改为新数据库用户
define( 'DB_PASSWORD', '你的数据库密码' );    // 改为新数据库密码
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

// ** 安全密钥（建议重新生成）** //
// 访问 https://api.wordpress.org/secret-key/1.1/salt/ 生成新的密钥
define( 'AUTH_KEY',         '...' );
define( 'SECURE_AUTH_KEY',  '...' );
define( 'LOGGED_IN_KEY',    '...' );
define( 'NONCE_KEY',        '...' );
define( 'AUTH_SALT',        '...' );
define( 'SECURE_AUTH_SALT', '...' );
define( 'LOGGED_IN_SALT',   '...' );
define( 'NONCE_SALT',       '...' );

$table_prefix = 'wp_';

define( 'WP_DEBUG', false );

// ** 站点地址（根据实际域名修改）** //
define( 'WP_HOME', 'https://www.miyaui.com' );
define( 'WP_SITEURL', 'https://www.miyaui.com' );

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
```

> **重要**: 如果域名变更，需要同时修改 `WP_HOME` 和 `WP_SITEURL`

---

### 四、数据库导入

#### 4.1 导出原数据库

```bash
mysqldump -u www_miyaui_com -p www_miyaui_com > miyaui_backup.sql
```

#### 4.2 导入新数据库

```bash
mysql -u 新数据库用户 -p 新数据库名 < miyaui_backup.sql
```

#### 4.3 修改数据库中的域名（如果域名变更）

```sql
-- 修改站点地址
UPDATE wp_options SET option_value = 'https://新域名.com' WHERE option_name = 'home';
UPDATE wp_options SET option_value = 'https://新域名.com' WHERE option_name = 'siteurl';

-- 修改文章中的链接
UPDATE wp_posts SET post_content = REPLACE(post_content, 'https://www.miyaui.com', 'https://新域名.com');
UPDATE wp_posts SET post_content = REPLACE(post_content, 'http://www.miyaui.com', 'https://新域名.com');

-- 修改文章 GUID
UPDATE wp_posts SET guid = REPLACE(guid, 'https://www.miyaui.com', 'https://新域名.com');

-- 修改评论中的链接
UPDATE wp_comments SET comment_content = REPLACE(comment_content, 'https://www.miyaui.com', 'https://新域名.com');
UPDATE wp_comments SET comment_author_url = REPLACE(comment_author_url, 'https://www.miyaui.com', 'https://新域名.com');

-- 修改 postmeta
UPDATE wp_postmeta SET meta_value = REPLACE(meta_value, 'https://www.miyaui.com', 'https://新域名.com');
```

> 推荐使用 **Velvet Blues Update URLs** 插件（已安装）在后台批量替换 URL

---

### 五、Nginx 配置

#### 5.1 反向代理架构（推荐，与原服务器一致）

**前端代理配置** `/www/server/panel/vhost/nginx/0.proxy_www.miyaui.com.conf`：

```nginx
server
{
    listen 80;
    listen 443 ssl;
    listen [::]:443 ssl;
    http2 on;
    server_name www.miyaui.com;

    ssl_certificate    /www/server/panel/vhost/cert/www.miyaui.com/fullchain.pem;
    ssl_certificate_key    /www/server/panel/vhost/cert/www.miyaui.com/privkey.pem;
    ssl_protocols TLSv1.1 TLSv1.2 TLSv1.3;
    ssl_ciphers EECDH+CHACHA20:EECDH+CHACHA20-draft:EECDH+AES128:RSA+AES128:EECDH+AES256:RSA+AES256:EECDH+3DES:RSA+3DES:!MD5;
    ssl_prefer_server_ciphers on;
    ssl_session_tickets on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    add_header Strict-Transport-Security "max-age=31536000";
    error_page 497  https://$host$request_uri;

    location /
    {
        proxy_pass http://127.0.0.1:8081;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

**后端服务配置** `/www/server/panel/vhost/nginx/www.miyaui.com.conf`：

```nginx
server
{
    listen 8081;
    server_name www.miyaui.com;
    index index.php index.html index.htm default.php default.htm default.html;
    root /www/wwwroot/www.miyaui.com;

    # PHP 配置
    include enable-php-80.conf;

    # WordPress 伪静态
    location /
    {
        try_files $uri $uri/ /index.php?$args;
    }

    rewrite /wp-admin$ $scheme://$host$uri/ permanent;

    # 禁止访问敏感文件
    location ~ ^/(\.user.ini|\.htaccess|\.git|\.env|\.svn|\.project|LICENSE|README.md)
    {
        return 404;
    }

    # SSL 证书验证目录
    location ~ \.well-known{
        allow all;
    }

    # 防盗链配置（根据需要修改）
    location ~ .*\.(jpg|jpeg|gif|png|js|css|webp|svg)$
    {
        expires      30d;
        access_log /dev/null;
        valid_referers none blocked www.miyaui.com miyaui.com;
        if ($invalid_referer){
           return 404;
        }
    }

    # 静态资源缓存
    location ~ .*\.(gif|jpg|jpeg|png|bmp|swf)$
    {
        expires      30d;
        error_log /dev/null;
        access_log /dev/null;
    }

    location ~ .*\.(js|css)?$
    {
        expires      12h;
        error_log /dev/null;
        access_log /dev/null;
    }

    access_log  /www/wwwlogs/www.miyaui.com.log;
    error_log  /www/wwwlogs/www.miyaui.com.error.log;
}
```

#### 5.2 简单架构（不使用反向代理）

如果不需要反向代理，直接用一个 server 块：

```nginx
server
{
    listen 80;
    listen 443 ssl;
    http2 on;
    server_name www.miyaui.com;
    index index.php index.html index.htm;
    root /www/wwwroot/www.miyaui.com;

    ssl_certificate    /path/to/fullchain.pem;
    ssl_certificate_key    /path/to/privkey.pem;
    ssl_protocols TLSv1.1 TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers on;

    # WordPress 伪静态
    location /
    {
        try_files $uri $uri/ /index.php?$args;
    }

    # PHP 处理
    location ~ [^/]\.php(/|$)
    {
        try_files $uri =404;
        fastcgi_pass  unix:/tmp/php-cgi-80.sock;
        fastcgi_index index.php;
        include fastcgi.conf;
        include pathinfo.conf;
    }

    # 禁止访问敏感文件
    location ~ ^/(\.user.ini|\.htaccess|\.git|\.env|\.svn|\.project)
    {
        return 404;
    }
}
```

---

### 六、Redis 配置

#### 6.1 安装 Redis

```bash
# 宝塔面板一键安装 Redis
# 或手动安装
yum install redis -y
systemctl start redis
systemctl enable redis
```

#### 6.2 WordPress Redis 插件

Redis 插件已安装在 `wp-content/plugins/redis-cache/`，object-cache.php drop-in 已在 `wp-content/object-cache.php`。

启用方式：
1. 登录 WordPress 后台
2. 进入 **设置 → Redis**
3. 点击 **启用对象缓存**

如果 Redis 连接不是默认配置，在 `wp-config.php` 中添加：

```php
define('WP_REDIS_HOST', '127.0.0.1');
define('WP_REDIS_PORT', 6379);
// define('WP_REDIS_PASSWORD', '你的Redis密码');
```

---

### 七、已安装插件清单

以下是当前激活的插件，部署时需确保这些插件目录存在：

| 插件目录 | 插件名称 | 用途 |
|----------|----------|------|
| `b2-app/` | B2 APP | B2 主题配套 APP 支持 |
| `b2_tongji/` | B2 统计 | 网站访问统计 |
| `classic-editor/` | 经典编辑器 | 恢复经典 WordPress 编辑器 |
| `feng-custom/` | 风自定义 | 自定义功能扩展 |
| `imgspider/` | 图片爬虫 | 图片采集工具 |
| `redis-cache/` | Redis Object Cache | Redis 对象缓存 |
| `wp-smtp/` | WP SMTP | 邮件发送 |

其他已安装但未激活的插件（按需启用）：
- `anti-account-sharing/` - 账号共享检测
- `auto-comment-plugin/` - 自动评论
- `b2-translate/` - B2 翻译
- `b2-zibtj/` - B2 资源统计
- `devskyr_rn_b2_tz_plugin/` - B2 通知插件
- `dynamic-island/` - 灵动岛效果
- `velvet-blues-update-urls/` - URL 批量更新
- `wp-admin-theme-cd/` - 后台主题美化
- `wp-netdisk-link-checker/` - 网盘链接检测
- `xhtheme-ai-toolbox/` - AI 工具箱
- `xmw-wechat-login/` - 微信登录
- `yet-another-related-posts-plugin/` - 相关文章

---

### 八、主题说明

#### 8.1 B2 父主题 (v5.9.42)

- 路径：`wp-content/themes/b2/`
- 类型：商业主题，需要许可证
- 许可证文件：`wp-content/themes/b2Jitheme/license.lic`
- 依赖：**Swoole Loader 3.2.1 扩展**（必须安装）

#### 8.2 Jitheme 子主题 (v2.9.0)

- 路径：`wp-content/themes/b2Jitheme/`
- 激活主题：`b2Jitheme`（子主题）
- 父主题：`b2`
- 自定义功能：
  - 自动重命名上传文件（按时间+随机数）
  - 删除文章时自动删除附件
  - 标签自动添加链接
  - 回到顶部按钮
  - 公告系统（自定义文章类型 `announcement`）
  - 自定义 REST API：`/wp-json/custom/v1/latest-announcements`

---

### 九、wp-config.php 关键配置说明

```php
// 反向代理 HTTPS 识别（必须！否则会出现 Mixed Content 错误）
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// 站点地址
define( 'WP_HOME', 'https://www.miyaui.com' );
define( 'WP_SITEURL', 'https://www.miyaui.com' );
```

---

### 十、PHP-FPM 配置

PHP 8.0 FPM 配置参考：

```ini
[www]
listen = /tmp/php-cgi-80.sock
listen.owner = www
listen.group = www
listen.mode = 0600
user = www
group = www
pm = dynamic
pm.max_children = 100
pm.start_servers = 10
pm.min_spare_servers = 10
pm.max_spare_servers = 30
request_terminate_timeout = 100
```

---

### 十一、部署后检查清单

- [ ] PHP 8.0 已安装且 swoole_loader 扩展已启用
- [ ] MySQL 5.7 数据库已导入
- [ ] Redis 服务已启动
- [ ] Nginx 配置已加载且无语法错误
- [ ] SSL 证书已配置且 HTTPS 正常
- [ ] `wp-config.php` 中数据库连接信息正确
- [ ] `wp-config.php` 中包含反向代理 HTTPS 识别代码
- [ ] 站点根目录权限为 `www:www`
- [ ] 后台可正常登录：`https://www.miyaui.com/wp-admin/`
- [ ] 前台页面正常加载，无 Mixed Content 错误
- [ ] Redis 对象缓存已启用
- [ ] 伪静态规则生效（文章页、分类页可正常访问）

---

### 十二、常见问题

#### 12.1 Mixed Content 错误

**症状**：页面加载了 HTTP 资源，浏览器控制台报错

**解决**：确保 `wp-config.php` 包含：
```php
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}
```

#### 12.2 B2 主题白屏 / 报错 "安装扩展"

**症状**：前台白屏，提示需要安装扩展

**解决**：检查 swoole_loader 是否正确安装：
```bash
/www/server/php/80/bin/php -m | grep swoole
```

#### 12.3 `b2_search_data is not defined` 错误

**症状**：浏览器控制台报 JavaScript 错误

**解决**：确保子主题 `b2Jitheme/Modules/Templates/Main.php` 第 291 行后有：
```php
wp_add_inline_script( 'b2-js-main', 'var b2_search_data = {"users":[]};', 'before' );
```

#### 12.4 Redis 连接失败

**症状**：后台 Redis 页面显示连接错误

**解决**：
```bash
systemctl start redis
systemctl enable redis
```

#### 12.5 404 错误（文章页、分类页）

**解决**：确保 Nginx 包含伪静态规则：
```nginx
location /
{
    try_files $uri $uri/ /index.php?$args;
}
```

---

### 十三、备份策略

#### 数据库备份
```bash
mysqldump -u www_miyaui_com -p www_miyaui_com > /backup/miyaui_$(date +%Y%m%d).sql
```

#### 文件备份
```bash
tar -czf /backup/miyaui_files_$(date +%Y%m%d).tar.gz /www/wwwroot/www.miyaui.com/
```

#### 建议设置定时备份
```bash
# 每天凌晨 3 点备份数据库
0 3 * * * mysqldump -u www_miyaui_com -p密码 www_miyaui_com > /backup/db_$(date +\%Y\%m\%d).sql
# 每周日凌晨 4 点备份文件
0 4 * * 0 tar -czf /backup/files_$(date +\%Y\%m\%d).tar.gz /www/wwwroot/www.miyaui.com/
```

---

## 快速部署命令汇总

```bash
# 1. 上传网站文件到 /www/wwwroot/www.miyaui.com/
# 2. 设置权限
chown -R www:www /www/wwwroot/www.miyaui.com/

# 3. 导入数据库
mysql -u 用户名 -p 数据库名 < miyaui_backup.sql

# 4. 修改 wp-config.php 中的数据库信息和域名

# 5. 配置 Nginx（参考第五节）

# 6. 配置 SSL 证书

# 7. 安装 swoole_loader（参考 1.3 节）

# 8. 启动 Redis
systemctl start redis && systemctl enable redis

# 9. 重启 PHP-FPM 和 Nginx
/etc/init.d/php-fpm-80 restart
nginx -s reload

# 10. 访问网站验证
curl -I https://www.miyaui.com/
```
