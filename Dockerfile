# 使用官方 PHP 8.3 CLI 镜像
FROM php:8.3-cli

# 安装系统依赖
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# 安装 PHP 扩展
RUN docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd zip

# 安装 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 设置工作目录
WORKDIR /app

# 复制项目文件
COPY . .

# 安装 PHP 依赖
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 复制启动脚本
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Railway 会通过环境变量 PORT 指定端口

ENTRYPOINT ["docker-entrypoint.sh"]
