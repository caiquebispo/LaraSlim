# Stage 1: Build dependencies
FROM composer:2 AS builder

WORKDIR /app

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install dependencies without dev packages for production
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy application code
COPY . .

# Generate optimized autoloader
RUN composer dump-autoload --optimize --no-dev

# Stage 2: Production image
FROM php:8.4-apache AS production

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    curl \
    && docker-php-ext-install zip pdo pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install SQLite support
RUN apt-get update && apt-get install -y libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configure PHP for production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Set PHP configurations
RUN echo "expose_php = Off" >> "$PHP_INI_DIR/conf.d/security.ini" \
    && echo "display_errors = Off" >> "$PHP_INI_DIR/conf.d/security.ini" \
    && echo "log_errors = On" >> "$PHP_INI_DIR/conf.d/security.ini" \
    && echo "error_log = /var/log/php_errors.log" >> "$PHP_INI_DIR/conf.d/security.ini"

# Configure Apache
COPY ./public/apache.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite headers

# Create non-root user for security
RUN useradd -u 1000 -ms /bin/bash laraslim

# Set working directory
WORKDIR /var/www/html

# Copy application from builder
COPY --from=builder /app .

# Create required directories
RUN mkdir -p storage/logs storage/cache storage/database \
    && touch storage/database/database.sqlite

# Set permissions
RUN chown -R laraslim:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 storage

# Switch to non-root user for runtime
# Note: Apache still runs as www-data, but files are owned by laraslim

EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

CMD ["apache2-foreground"]

# Stage 3: Development image
FROM php:8.4-apache AS development

# Install system dependencies (including dev tools)
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    curl \
    git \
    && docker-php-ext-install zip pdo pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install SQLite support
RUN apt-get update && apt-get install -y libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# Configure PHP for development
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Configure Apache
COPY ./public/apache.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite headers

# Set working directory
WORKDIR /var/www/html

# Application will be mounted as volume in development

EXPOSE 80

CMD ["apache2-foreground"]