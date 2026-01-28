FROM ubuntu:24.04

ARG NODE_VERSION="22"
ARG NVM_VERSION="v0.40.3"
ENV DEBIAN_FRONTEND=noninteractive
ENV SHELL="/bin/bash"

# Install PHP 8.3 and required extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    apache2 \
    libapache2-mod-php \
    php \
    php-mysql \
    php-mbstring \
    php-xml \
    php-curl \
    php-zip \
    php-gd \
    php-intl \
    php-bcmath \
    php-sqlite3 \
    php-xdebug \
    curl \
    git \
    unzip \
    sudo \
    build-essential \
    ca-certificates \
    gnupg \
    lsb-release \
    procps \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer

# Fix Ubuntu 24.04 user conflict (rename default 'ubuntu' user to 'dev')
RUN usermod -l dev ubuntu && \
    groupmod -n dev ubuntu && \
    usermod -d /home/dev -m dev && \
    echo "dev:dev" | chpasswd && \
    echo "dev ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

# Configure Apache: Enable rewrite and update site config
RUN a2enmod rewrite && \
    sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf && \
    sed -i '/DocumentRoot \/var\/www\/html\/public/a \
    <Directory /var/www/html/public>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>' /etc/apache2/sites-available/000-default.conf

# Run Apache as the 'dev' user to avoid permission headaches
RUN sed -i 's/export APACHE_RUN_USER=www-data/export APACHE_RUN_USER=dev/' /etc/apache2/envvars && \
    sed -i 's/export APACHE_RUN_GROUP=www-data/export APACHE_RUN_GROUP=dev/' /etc/apache2/envvars

USER dev
WORKDIR /home/dev
ENV NVM_DIR="/home/dev/.nvm"

# Optional: Install Node/NVM (useful for JS tools even without Vite)
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/${NVM_VERSION}/install.sh | bash && \
    bash -c "source $NVM_DIR/nvm.sh && nvm install ${NODE_VERSION} && nvm alias default ${NODE_VERSION}"

WORKDIR /var/www/html

COPY --chown=dev:dev docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["sudo", "/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
