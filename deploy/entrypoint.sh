#!/bin/sh

set -e

# 创建并确保 /var/log/exported 目录存在
mkdir -p /var/log/exported
chmod -R 775 /var/log/exported

# 复制 vector 配置文件
if [ ! -f /var/log/exported/vector.toml ]; then
  cp /var/www/html/deploy/vector.toml /var/log/exported/vector.toml
fi

# 删除部署配置目录
rm -rf /var/www/html/deploy

# 启动 Nginx 和 PHP-FPM
exec "$@"