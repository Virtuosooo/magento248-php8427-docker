#!/usr/bin/env bash
set -euo pipefail
if [ -e .env ]; then
    echo '.env already exists; keeping existing credentials.'
    exit 1
fi
umask 077
{
    echo 'MAGENTO_IMAGE=ghcr.io/virtuosooo/magento248-php8427:2.4.8-php8.4.27'
    echo "DB_PASSWORD=$(openssl rand -hex 24)"
    echo "DB_ROOT_PASSWORD=$(openssl rand -hex 24)"
    echo "ADMIN_PASSWORD=$(openssl rand -hex 24)"
} > .env
echo 'Created .env with independent local test credentials.'
