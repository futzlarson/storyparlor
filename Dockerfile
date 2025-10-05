FROM richarvey/nginx-php-fpm:3.1.6

COPY . .

# Fix nginx config for Laravel routing
RUN sed -i 's|try_files $uri $uri/ =404;|try_files $uri $uri/ /index.php?$query_string;|g' /etc/nginx/sites-available/default.conf

# Image config
ENV SKIP_COMPOSER=1
ENV WEBROOT=/var/www/html/public
ENV PHP_ERRORS_STDERR=1
ENV RUN_SCRIPTS=1
ENV REAL_IP_HEADER=1

# Allow composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install dependencies
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader

# Runtime commands
RUN chmod +x init.sh && mv init.sh /init.sh
ENTRYPOINT ["/init.sh"]
