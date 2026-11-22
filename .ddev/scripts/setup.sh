#!/usr/bin/env bash
# Idempotent local setup, run by the DDEV post-start hook inside the web container.
set -euo pipefail
cd /var/www/html
# WP-CLI itself still emits PHP 8.5 deprecation notices; silence only those, only for WP-CLI.
WP="php -d error_reporting=24575 /usr/local/bin/wp --path=wordpress"

# Database for the WordPress integration test suite (created first: tests need it even if an
# optional step below fails).
mysql -uroot -proot -hdb -e "CREATE DATABASE IF NOT EXISTS wordpress_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL ON wordpress_test.* TO 'db'@'%';" 2>/dev/null

if [ ! -f wordpress/wp-includes/version.php ]; then
  echo "EuTeAjudo: downloading WordPress"
  $WP core download --skip-content
fi
# --skip-content leaves wp-content without uploads/, which WordPress and its test suite need.
mkdir -p wordpress/wp-content/uploads

if ! $WP core is-installed 2>/dev/null; then
  echo "EuTeAjudo: installing WordPress (local admin: admin / admin)"
  $WP core install --url="${DDEV_PRIMARY_URL}" --title="EuTeAjudo.org (local)" \
    --admin_user=admin --admin_password=admin --admin_email=admin@euteajudo.ddev.site --skip-email
  $WP option update timezone_string America/Sao_Paulo
  $WP rewrite structure '/%postname%/' --hard
  # Optional conveniences: they depend on wordpress.org being reachable, so a failure only warns.
  $WP language core install pt_BR --activate || echo "EuTeAjudo: warning: pt_BR language not installed"
  $WP plugin install query-monitor --activate || echo "EuTeAjudo: warning: Query Monitor not installed"
fi

# Pretty permalinks need .htaccess on Apache (also used by /wp-json/ routes).
[ -f wordpress/.htaccess ] || $WP rewrite flush --hard

$WP theme is-active euteajudo-theme 2>/dev/null || $WP theme activate euteajudo-theme
$WP plugin is-active euteajudo 2>/dev/null || $WP plugin activate euteajudo

echo "EuTeAjudo: ready at ${DDEV_PRIMARY_URL}"
