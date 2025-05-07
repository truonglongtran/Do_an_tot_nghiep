FROM php:8.2-fpm

# Cài đặt các dependencies cần thiết
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libonig-dev libxml2-dev \
    libpng-dev libjpeg-dev libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Cài đặt các PHP extensions (cài đặt tokenizer trước)
RUN docker-php-ext-install pdo pdo_mysql mbstring xml gd

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Đặt thư mục làm việc trong container
WORKDIR /var/www

# Copy mã nguồn Laravel vào container
COPY . .

# Cài đặt dependencies của Laravel
RUN composer install

# Expose port cho Laravel (8000)
EXPOSE 8000

# Command chạy Laravel (sử dụng PHP-FPM)
CMD ["php-fpm"]
