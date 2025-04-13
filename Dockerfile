FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy bootstrap script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

# Copy existing application directory contents
COPY . /var/www

# Assign permissions to the Laravel storage path
RUN chmod -R 777 /var/www/storage

# Expose port 9000
EXPOSE 9000

# Set entrypoint
ENTRYPOINT ["docker-entrypoint"]

# Start php-fpm server
CMD ["php-fpm"] 