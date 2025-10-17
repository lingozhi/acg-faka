#!/bin/bash
set -e

# Railway 提供的 PORT 环境变量，如果没有则使用 80
PORT=${PORT:-80}

# 更新 Apache 配置以监听指定端口
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/" /etc/apache2/sites-available/*.conf

# 执行传入的命令
exec "$@"
