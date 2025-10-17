#!/bin/bash
set -e

# Railway 提供的 PORT 环境变量，如果没有则使用 8080
PORT=${PORT:-8080}

echo "Starting PHP built-in server on 0.0.0.0:${PORT}..."

# 启动 PHP 内置服务器，使用 router.php 处理路由（支持伪静态）
exec php -S 0.0.0.0:${PORT} -t /app /app/router.php
