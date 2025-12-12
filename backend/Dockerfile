FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    default-mysql-client \
 && docker-php-ext-install pdo_mysql opcache \
 && rm -rf /var/lib/apt/lists/*

 # Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /srv

# Install dependencies
COPY composer.json composer.lock* ./personal-finance-tracker/backend/
RUN composer install --no-interaction --prefer-dist -d ./personal-finance-tracker/backend/

# Copy source
COPY . ./personal-finance-tracker/backend/

# Expose php port
EXPOSE 80

# Register the admin and start the server
CMD ["sh", "-c", "php ./personal-finance-tracker/backend/services/admin-creation-script.php && php -S 0.0.0.0:80 -t ."]