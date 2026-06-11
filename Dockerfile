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

# Definir DocumentRoot para public/ e habilitar AllowOverride
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|<Directory /var/www/html>|<Directory /var/www/html/public>|g' /etc/apache2/apache2.conf \
    && printf '<Directory /var/www/html>\n\tOptions FollowSymLinks\n\tAllowOverride None\n\tRequire all granted\n</Directory>\n<Directory /var/www/html/public>\n\tAllowOverride All\n\tRequire all granted\n</Directory>\n' \
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

# Instalar dependências PHP e guardar cópia em /opt/vendor
# (fora do bind mount) para o entrypoint restaurar localmente
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
    && cp -r vendor /opt/vendor

# Copiar ficheiros da aplicação para a imagem
COPY . .

# Entrypoint: restaura vendor/ se estiver em falta (bind mount sem composer)
# e ajusta o porto do Apache conforme PORT do Railway.
# Criado aqui (não copiado do host) para evitar problemas de CRLF no Windows.
RUN printf '#!/bin/sh\nset -e\n\
if [ ! -f /var/www/html/vendor/autoload.php ]; then\n\
  echo "[entrypoint] vendor/ nao encontrado, a restaurar de /opt/vendor..."\n\
  cp -r /opt/vendor /var/www/html/vendor\n\
fi\n\
find /etc/apache2/mods-enabled -name '"'"'mpm_event*'"'"'  -delete 2>/dev/null || true\n\
find /etc/apache2/mods-enabled -name '"'"'mpm_worker*'"'"' -delete 2>/dev/null || true\n\
sed -i "s/^Listen .*/Listen ${PORT:-80}/" /etc/apache2/ports.conf\n\
sed -i "s/<VirtualHost \\*:[0-9][0-9]*/<VirtualHost *:${PORT:-80}/" /etc/apache2/sites-available/000-default.conf\n\
exec "$@"\n' > /docker-entrypoint-sylora.sh \
    && chmod +x /docker-entrypoint-sylora.sh

EXPOSE 80

ENTRYPOINT ["/docker-entrypoint-sylora.sh"]
CMD ["apache2-foreground"]
