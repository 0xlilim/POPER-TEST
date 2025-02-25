# deploy/Dockerfile

# stage 1: build stage
FROM php:8.4-fpm-alpine AS build

# 安装系统依赖和 PHP 扩展 (包含构建和生产环境需要的)
RUN apk add --no-cache \
    zip \
    libzip-dev \
    freetype \
    libjpeg-turbo \
    libpng \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    nodejs \
    npm \
    oniguruma-dev \
    gettext-dev

RUN docker-php-ext-configure zip \
    && docker-php-ext-install zip pdo pdo_mysql gd bcmath exif gettext opcache \
    && docker-php-ext-enable gd bcmath exif gettext opcache

# 安装 composer
COPY --from=composer:2.7.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 复制必要文件并修改权限
COPY . .
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 安装 php 及依赖
RUN composer install --no-dev --prefer-dist

# 清理构建缓存
RUN rm -rf $HOME/.composer/cache && npm cache clean --force

# stage 2: production stage
FROM php:8.4-fpm-alpine

# 安装生产环境需要的依赖 (Nginx)
RUN apk add --no-cache nginx

WORKDIR /var/www/html

# 复制 build 阶段的文件
COPY --from=build /var/www/html /var/www/html
COPY ./deploy/nginx.conf /etc/nginx/http.d/default.conf
COPY ./deploy/php.ini /usr/local/etc/php/conf.d/app.ini
COPY ./deploy/www.conf /usr/local/etc/php-fpm.d/www.conf


# 复制 entrypoint 脚本
COPY ./deploy/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh


# 添加持久化存储的目录
# VOLUME ["/var/www/html/storage/app"]
VOLUME ["/var/log/exported"]


HEALTHCHECK --interval=30s --timeout=5s --retries=3 \
    CMD curl -f http://localhost/up || exit 1

# 调用 entrypoint 脚本 & 启动服务
CMD ["sh", "-c", "/usr/local/bin/entrypoint.sh nginx && php-fpm"]