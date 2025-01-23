#!/bin/sh
set -e

mkdir -p /var/log/exported
chown www-data:www-data /var/log/exported 
chmod 775 /var/log/exported

mkdir -p /opt/aws/appconfig/
chown -R www-data:www-data /opt/aws/appconfig/
chmod -R 775 /opt/aws/appconfig/

chown -R www-data:www-data /var/www/html/storage/logs/

# 复制 vector 配置文件
if [ ! -f /var/log/exported/vector.toml ]; then
  cp /var/www/html/deploy/vector.toml /var/log/exported/vector.toml
fi

# 删除部署配置目录
rm -rf /var/www/html/deploy

# 启动 Nginx 和 PHP-FPM
exec "$@"