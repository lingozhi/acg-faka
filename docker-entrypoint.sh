#!/bin/bash
set -e

# Railway 提供的 PORT 环境变量，如果没有则使用 80
export PORT=${PORT:-80}

echo "Configuring Apache to listen on port ${PORT}..."

# 更新 Apache 端口配置
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf

# 更新 VirtualHost 配置
for conf_file in /etc/apache2/sites-available/*.conf; do
    if [ -f "$conf_file" ]; then
        sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g" "$conf_file"
        sed -i "s/:80/:${PORT}/g" "$conf_file"
    fi
done

echo "Apache configured to listen on port ${PORT}"

# 执行传入的命令
exec "$@"
