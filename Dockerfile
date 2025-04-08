# Use PHP with Apache
FROM php:8.2-apache

# Enable Apache mod_rewrite (for pretty URLs)
RUN a2enmod rewrite

# Install dependencies required for PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli pdo_pgsql pgsql

# Copy everything into the web server directory
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html

# Expose Apache port
EXPOSE 80
