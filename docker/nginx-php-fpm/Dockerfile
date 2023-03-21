FROM keelearning.azurecr.io/keelearning/backend-docker-container/keelearning-backend:0.47

# Copy our files
COPY . /var/www/html
RUN composer install && \
    npm ci && \
    npm run production && \
    php artisan route:cache && \
    php artisan config:clear && \
    php artisan update:geolite && \
    chown -R www-data:www-data ./public/storage && \
    chown -R www-data:www-data ./storage && \
    chown -R www-data:www-data ./bootstrap
