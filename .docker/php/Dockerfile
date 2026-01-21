#  ===============================================================================
# Dockerfile for Etra Server v2 - Multi-Application PHP Server
# ================================================================================

# =============================================================================
# _Dockerfile for Etra Server v2 - Multi-Application PHP Server_
# ============================================================================
#
# This Dockerfile containerizes a multi-application PHP server with:
# - foodie.app, almo.app, etra.group, superviral.io, superviral.app,
#   tikoid.com, keyshock.com
# - PHP 8.1 with Apache
# - MySQL/MariaDB support (mysqli)
# - Redis support for sessions and caching
# - AWS SDK integration
# - All required PHP extensions
#
# Build: docker build -t etra-server-v2:latest .
# Run:   docker run -p 80:80 --env-file .env etra-server-v2:latest
#
# Required Environment Variables:
# - DB_HOST, DB_USER, DB_PASS (Database)
# - REDIS_HOST, REDIS_PORT (Redis)
# - AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY (AWS)
# - See .env.example for complete list
# ============================================================================#

FROM php:8.1-apache

WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    wget \
    vim \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    libcurl4-openssl-dev \
    libssl-dev \
    redis-tools \
    netcat-openbsd \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mysqli \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    opcache

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Enable Apache modules
RUN a2enmod rewrite headers expires deflate ssl

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configure PHP (inline - no external files needed)
RUN { \
    echo 'memory_limit = 512M'; \
    echo 'max_execution_time = 300'; \
    echo 'max_input_time = 300'; \
    echo 'upload_max_filesize = 50M'; \
    echo 'post_max_size = 50M'; \
    echo 'date.timezone = Europe/London'; \
    echo 'log_errors = On'; \
    echo 'display_errors = Off'; \
    echo 'error_log = /var/log/apache2/php_errors.log'; \
    echo 'opcache.enable = 1'; \
    echo 'opcache.memory_consumption = 256'; \
    echo 'opcache.interned_strings_buffer = 16'; \
    echo 'opcache.max_accelerated_files = 10000'; \
    } > /usr/local/etc/php/conf.d/custom.ini

# Configure Apache (inline - no external files needed)
RUN { \
    echo '<VirtualHost *:80>'; \
    echo '    ServerAdmin admin@etra.group'; \
    echo '    DocumentRoot /var/www/html/superviral.io'; \
    echo '    '; \
    echo '    <Directory /var/www/html/superviral.io>'; \
    echo '        Options +FollowSymLinks +Indexes'; \
    echo '        AllowOverride All'; \
    echo '        Require all granted'; \
    echo '        DirectoryIndex index.php index.html'; \
    echo '    </Directory>'; \
    echo '    '; \
    echo '    # Protect sensitive files'; \
    echo '    <FilesMatch "^\.env">'; \
    echo '        Require all denied'; \
    echo '    </FilesMatch>'; \
    echo '    '; \
    echo '    # Enable compression'; \
    echo '    <IfModule mod_deflate.c>'; \
    echo '        AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json'; \
    echo '    </IfModule>'; \
    echo '    '; \
    echo '    ErrorLog ${APACHE_LOG_DIR}/error.log'; \
    echo '    CustomLog ${APACHE_LOG_DIR}/access.log combined'; \
    echo '</VirtualHost>'; \
    } > /etc/apache2/sites-available/000-default.conf

# Copy application files (context should be repository root, not .docker/php/)
# Build command should be: docker build -f .docker/php/Dockerfile .
# NOT: docker build -f .docker/php/Dockerfile .docker/php
COPY . /var/www/html/

# Copy prestart checks script and make sure log exists
COPY .docker/php/prestart_checks.sh /usr/local/bin/prestart_checks.sh
RUN chmod +x /usr/local/bin/prestart_checks.sh \
    && mkdir -p /var/log \
    && touch /var/log/prestart_checks.log \
    && chown www-data:www-data /var/log/prestart_checks.log

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

# Expose ports
EXPOSE 80 443

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Start Apache but run prestart checks first
CMD ["/bin/bash", "-c", "/usr/local/bin/prestart_checks.sh || { echo 'Prestart checks failed; aborting'; exit 1; } && exec apache2-foreground"]
