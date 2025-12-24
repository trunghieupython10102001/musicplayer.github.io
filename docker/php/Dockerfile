# PHP-FPM Docker Image for Music Player

FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    unzip \
    mysql-client \
    msmtp

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    mysqli \
    gd \
    exif

# Set working directory
WORKDIR /var/www/html

# PHP configuration for email and uploads
RUN echo "upload_max_filesize = 100M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini

# Configure sendmail for MailHog via msmtp
RUN echo "sendmail_path = '/usr/bin/msmtp -C /etc/msmtprc -t'" >> /usr/local/etc/php/conf.d/mail.ini

# Create msmtp configuration
RUN echo "account default\nhost musicplayer_mailhog\nport 1025\nfrom noreply@musicplayer.local\ntls off\nauto_from on" > /etc/msmtprc && \
    chmod 644 /etc/msmtprc

# Copy entrypoint script
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html

# Expose PHP-FPM port
EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

