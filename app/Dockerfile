# =============================================================================
# OpenCart 3.8.0.0 (opencart.cn) — Linux + Nginx + PHP-FPM
# 单容器：Nginx 作为 Web 服务器，PHP-FPM 处理 PHP，supervisord 托管两个进程
# =============================================================================
FROM php:7.4-fpm-bullseye

ENV TZ=Asia/Tokyo \
    COMPOSER_ALLOW_SUPERUSER=1

# 安装系统依赖：nginx、supervisord、curl(健康检查)、PHP 扩展编译依赖
RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx \
        supervisor \
        curl \
        tzdata \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
        libcurl4-openssl-dev \
        zlib1g-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        gd \
        mysqli \
        pdo_mysql \
        curl \
        mbstring \
        zip \
        intl \
        bcmath \
        dom \
        xml \
        simplexml \
        opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 时区
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# 移除 Nginx 默认站点，避免与 OpenCart 站点冲突
RUN rm -f /etc/nginx/sites-enabled/default

# Nginx 站点配置
COPY docker/nginx/opencart.conf /etc/nginx/conf.d/opencart.conf

# PHP-FPM 与 PHP 运行参数
COPY docker/php-fpm/php.ini /usr/local/etc/php/conf.d/zz-opencart.ini

# supervisord 配置
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 应用代码
WORKDIR /var/www/html
COPY . /var/www/html

# 权限：整站归 www-data；config.php、image、storage 需可写
RUN mkdir -p /var/www/html/system/storage/cache \
             /var/www/html/system/storage/logs \
             /var/www/html/system/storage/download \
             /var/www/html/system/storage/session \
             /var/www/html/system/storage/upload \
             /var/www/html/system/storage/marketplace \
             /var/www/html/system/storage/backup \
             /var/www/html/image/cache \
             /var/www/html/image/catalog \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/system/storage \
    && chmod -R 775 /var/www/html/image \
    && chmod 666 /var/www/html/config.php \
    && chmod 666 /var/www/html/admin/config.php

EXPOSE 80

# 健康检查：Nginx 首页返回 200 视为健康
HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD curl -fsS http://127.0.0.1/ || exit 1

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]
