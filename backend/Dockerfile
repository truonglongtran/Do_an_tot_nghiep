FROM php:8.2-fpm

# Cài đặt các dependencies cần thiết
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libonig-dev libxml2-dev \
    libpng-dev libjpeg-dev libfreetype6-dev \
    cron nano \
    && rm -rf /var/lib/apt/lists/*

# Cài đặt các PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring xml gd

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy cron file
COPY docker/laravel.cron /etc/cron.d/laravel
RUN chmod 0644 /etc/cron.d/laravel && crontab /etc/cron.d/laravel && touch /var/log/cron.log

# Đặt thư mục làm việc trong container
WORKDIR /var/www

# Copy mã nguồn Laravel vào container
COPY . .

# Cài đặt dependencies, publish Sanctum, chạy storage:link và đặt quyền
RUN composer install && \
    php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider" --force && \
    rm -f /var/www/public/storage && \
    php artisan storage:link && \
    chown -R www-data:www-data /var/www/storage /var/www/public && \
    chmod -R 775 /var/www/storage /var/www/public

# Expose port cho Laravel
EXPOSE 8000

# ✅ Command: chạy cron và Laravel cùng lúc
CMD ["sh", "-c", "cron && php artisan serve --host=0.0.0.0 --port=8000"]
