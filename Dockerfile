FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

# 设置时区
ENV TZ=Asia/Tokyo

# 安装必要的依赖和扩展
RUN apk add --no-cache \
    nginx \
    supervisor \
    composer \
    libzip-dev \
    $PHPIZE_DEPS \
    oniguruma-dev \
    libxml2-dev && \
    docker-php-ext-install \
        pdo_mysql \
        zip \
        bcmath \
        fileinfo \
        dom && \
    docker-php-ext-configure \
        session && \
    apk del $PHPIZE_DEPS && \
    rm -rf /tmp/* /var/cache/apk/*

# 复制项目文件和配置
COPY . /var/www/html
COPY ./docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY ./docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY ./docker/php.ini /usr/local/etc/php/php.ini

# 设置目录权限
RUN chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

# 安装依赖
RUN composer install --no-dev --optimize-autoloader

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]