FROM php:8.3-apache

# Instalar extensões PHP necessárias (GD para redimensionar avatares)
RUN apt-get update && apt-get install -y \
        libfreetype6-dev libjpeg62-turbo-dev libpng-dev libwebp-dev \
        unzip zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd exif mysqli pdo pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Fixar MPM: mostrar o que está ativo, apagar event/worker, confirmar resultado
RUN echo "=== MPMs antes ===" && find /etc/apache2/mods-enabled -name 'mpm_*' | sort \
    && find /etc/apache2/mods-enabled -name 'mpm_event*' -delete \
    && find /etc/apache2/mods-enabled -name 'mpm_worker*' -delete \
    && echo "=== MPMs depois ===" && find /etc/apache2/mods-enabled -name 'mpm_*' | sort

# Ativar módulos Apache necessários
RUN a2enmod rewrite headers expires deflate

# Permitir que o .htaccess funcione (AllowOverride All)
RUN printf '<Directory /var/www/html>\n\tAllowOverride All\n\tRequire all granted\n</Directory>\n' \
    > /etc/apache2/conf-available/docker-override.conf \
    && a2enconf docker-override

# Configuração PHP: segurança + performance
RUN echo "output_buffering = 4096\ndefault_charset = UTF-8\nopcache.enable = 0\nmemory_limit = 256M\nupload_max_filesize = 5M\npost_max_size = 6M\ndisplay_errors = Off\nexpose_php = Off\nlog_errors = On" \
    > /usr/local/etc/php/conf.d/sylora.ini

# Instalar Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Diretório de trabalho
WORKDIR /var/www/html

# Instalar dependências PHP (camada separada para cache)
COPY composer.json .
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Copiar ficheiros da aplicação para a imagem
COPY . .

EXPOSE 80

# Ajustar porto do Apache ao valor de PORT definido pelo Railway em runtime
CMD find /etc/apache2/mods-enabled -name 'mpm_event*' -delete 2>/dev/null; \
    find /etc/apache2/mods-enabled -name 'mpm_worker*' -delete 2>/dev/null; \
    sed -i "s/Listen 80/Listen ${PORT:-80}/" /etc/apache2/ports.conf && \
    apache2-foreground
