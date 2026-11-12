# An image for testing the package on a specific version of PHP. Used by testing/run-all-php-versions.sh.
# Build with e.g.: docker build --build-arg PHP_VERSION=8.1 -t swagger-php8.1-test testing/php-versions
ARG PHP_VERSION=8.1
FROM php:${PHP_VERSION}-cli

# The yaml extension is required by the package (for OpenApi::toYaml()), but isn't in the official image, so it is
# built from PECL, which needs libyaml's headers.
RUN apt-get update -qq > /dev/null && apt-get install -y -qq libyaml-dev > /dev/null \
    && rm -rf /var/lib/apt/lists/* \
    && pecl install yaml > /dev/null \
    && docker-php-ext-enable yaml
