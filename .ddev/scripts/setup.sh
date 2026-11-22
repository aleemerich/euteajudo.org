#!/usr/bin/env bash
# Idempotent local setup, run by the DDEV post-start hook inside the web container.
set -euo pipefail
cd /var/www/html
# WP-CLI itself still emits PHP 8.5 deprecation notices; silence only those, only for WP-CLI.
WP="php -d error_reporting=24575 /usr/local/bin/wp --path=wordpress"

if [ ! -f wordpress/wp-includes/version.php ]; then
  echo "EuTeAjudo: downloading WordPress"
  $WP core download --skip-content
fi

if ! $WP core is-installed 2>/dev/null; then
  echo "EuTeAjudo: installing WordPress (local admin: admin / admin)"
  $WP core install --url="${DDEV_PRIMARY_URL}" --title="EuTeAjudo.org (local)" \
    --admin_user=admin --admin_password=admin --admin_email=admin@euteajudo.ddev.site --skip-email
  $WP language core install pt_BR --activate
  $WP option update timezone_string America/Sao_Paulo
  $WP rewrite structure '/%postname%/' --hard
  $WP plugin install query-monitor --activate
fi

$WP theme is-active euteajudo 2>/dev/null || $WP theme activate euteajudo
$WP plugin is-active euteajudo 2>/dev/null || $WP plugin activate euteajudo

# Database for the WordPress integration test suite.
mysql -uroot -proot -hdb -e "CREATE DATABASE IF NOT EXISTS wordpress_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL ON wordpress_test.* TO 'db'@'%';" 2>/dev/null
echo "EuTeAjudo: ready at ${DDEV_PRIMARY_URL}"
