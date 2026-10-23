# Magento 2.4.8 + PHP 8.4.27

独立本机测试商城：Magento Open Source **2.4.8**、PHP **8.4.27**、MariaDB **11.4**、OpenSearch **2.19.0**。
不含 RECON 插件、示例商品、商户凭据或已安装的数据库。不依赖 Mailcatcher。

## Windows / WSL2 部署

启用 Docker Desktop 的 WSL2 integration，在 Ubuntu 终端执行。建议 Docker 分配至少 6 GB 内存。
私有仓库和 GHCR 镜像需要自己的 GitHub 访问权限；`docker login ghcr.io` 的密码使用自己的只读 packages token，勿把 token 发给他人。

```bash
git clone https://github.com/Virtuosooo/magento248-php8427-docker.git
cd magento248-php8427-docker
bash setup-env.sh
docker login ghcr.io -u YOUR_GITHUB_USERNAME
docker compose pull
docker compose up -d --wait
docker compose exec --user www-data app php /usr/local/bin/install-magento.php
docker compose exec --user www-data app php bin/magento setup:static-content:deploy -f en_US
docker compose exec --user www-data app php bin/magento indexer:reindex
docker compose exec --user www-data app php bin/magento cache:flush
```

商城：https://localhost:8448/；后台：https://localhost:8448/admin/。
用户 `recon_admin`，管理员密码见本机 `.env` 的 `ADMIN_PASSWORD`。每次部署独立生成凭据和自签名证书。
后台双重验证保持 Magento 原版默认设置，首次登录按页面提示完成配置。

安装命令拒绝对已安装数据库重复执行。

停止：`docker compose stop`。再次启动：`docker compose up -d --wait`。保留 volumes 即保留数据。
仅绑定 127.0.0.1，不开放公网，因此不提供公网服务端支付回调。

## 镜像构建

构建用 Magento 程序与锁定依赖归档位于 `build-source/`，以分段文件保存，避免单文件超过 GitHub 大小限制。构建时还原 `magento-2.4.8-source.tar.gz`，SHA-256 由 `source.sha256` 核对。
该归档不含下载认证、安装后的配置或数据库。GHCR 由 GitHub Actions 使用临时 GITHUB_TOKEN 发布，不需要额外保存发布用个人 token。
手动运行 `Publish image` workflow，构建并发布 linux/amd64、linux/arm64 镜像。
构建结果与业务兼容性分开：干净商城可运行不代表 RECON 插件或支付流程通过。
