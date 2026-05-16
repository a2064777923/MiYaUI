# MiYaUI - 蜜芽设计

优质平面设计资源素材免费下载平台

## 简介

蜜芽设计 (www.miyaui.com) 是一个为设计师打造的设计资源分享网站，提供 UI 素材、平面设计、教程、样机、字体、PSD 模板等资源下载。

## 技术栈

| 组件 | 版本 |
|------|------|
| WordPress | 6.9.4 |
| PHP | 8.0 |
| MySQL | 5.7 |
| Nginx | 1.28 |
| B2 Theme | 5.9.42 |
| Jitheme | 2.9.0 |
| Redis | 对象缓存 |
| Swoole Loader | 3.2.1 |

## 项目结构

```
www.miyaui.com/
├── wp-admin/                  # WordPress 后台
├── wp-includes/               # WordPress 核心
├── wp-content/
│   ├── themes/
│   │   ├── b2/                # B2 父主题
│   │   └── b2Jitheme/         # Jitheme 子主题
│   ├── plugins/               # 插件目录
│   ├── uploads/               # 媒体文件 (不纳入版本控制)
│   └── object-cache.php       # Redis 缓存
├── wp-config.php              # 配置文件 (不纳入版本控制)
├── wp-config-sample.php       # 配置模板
├── DEPLOY.md                  # 部署文档
└── README.md                  # 本文件
```

## 快速部署

详细部署步骤请查看 [DEPLOY.md](./DEPLOY.md)

### 1. 环境要求

- Nginx 1.28+
- PHP 8.0 + Swoole Loader 3.2.1
- MySQL 5.7
- Redis

### 2. 部署

```bash
# 克隆仓库
git clone https://github.com/a2064777923/MiYaUI.git
cd MiYaUI

# 复制配置模板
cp wp-config-sample.php wp-config.php
# 编辑 wp-config.php 填入数据库信息

# 导入数据库
mysql -u 用户名 -p 数据库名 < backup.sql

# 设置权限
chown -R www:www .

# 配置 Nginx + SSL (参考 DEPLOY.md)
```

## 分支说明

| 分支 | 用途 |
|------|------|
| `main` | 主分支，当前线上版本 |
| `old-version` | 快照分支，部署前的原始状态 |

## 激活插件

- **B2 APP** - 主题配套 APP 支持
- **B2 统计** - 访问统计
- **经典编辑器** - WordPress 编辑器
- **风自定义** - 自定义功能扩展
- **图片爬虫** - 图片采集
- **Redis Object Cache** - 对象缓存
- **WP SMTP** - 邮件发送

## 许可证

B2 主题为商业主题，需要购买许可证。
