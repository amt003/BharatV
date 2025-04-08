# Use PHP 8.2 with Apache
FROM php:8.2-apache

# Enable Apache mod_rewrite (for clean URLs)
RUN a2enmod rewrite

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copy code from src/ into Apache root
COPY src/ /var/www/html/

# Set permissions (optional)
RUN chown -R www-data:www-data /var/www/html

# Set working directory
WORKDIR /var/www/html

# Expose Apache port
EXPOSE 80
