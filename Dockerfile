FROM php:8.1.23-fpm

# Install dependencies
RUN --mount=target=/var/lib/apt/lists,type=cache,sharing=locked \
    --mount=target=/var/cache/apt,type=cache,sharing=locked \
    rm -f /etc/apt/apt.conf.d/docker-clean \
    && echo 'Binary::apt::APT::Keep-Downloaded-Packages "true";' > /etc/apt/apt.conf.d/keep-cache \
    && apt-get update \
    && apt-get install -y --no-install-recommends git unzip libffi-dev libvips42

RUN docker-php-ext-configure ffi --with-ffi \
    && docker-php-ext-install -j$(nproc) ffi \
    && ldconfig

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
