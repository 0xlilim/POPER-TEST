FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

COPY . /var/www/html

ENV TZ=Asia/Tokyo

RUN apk update && apk add --no-cache composer nginx supervisor curl 

COPY nginx.conf /etc/nginx/conf.d/default.conf
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN composer install --no-dev --no-interaction --no-ansi --no-scripts --optimize-autoloader

EXPOSE 80

# 调用 /healthcheck 接口进行健康监测
HEALTHCHECK --interval=5s --timeout=3s --retries=3 CMD curl --fail http://localhost/healthcheck || exit 1

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]