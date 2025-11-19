# syntax=docker/dockerfile:1
FROM php:8.2-apache

# Instalar dependencias del sistema necesarias para las extensiones
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones requeridas de PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    mysqli \
    pdo \
    pdo_mysql \
    && docker-php-ext-enable mysqli pdo_mysql

# Configuración de Apache
RUN a2enmod rewrite

# Copiar configuración PHP
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Copiar el código (para builds de producción; en desarrollo se usará volumen)
COPY . /var/www/html

# Permisos para subidas (si las hay dentro del proyecto)
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

EXPOSE 80

# Comando por defecto
CMD ["apache2-foreground"]