# Minimal multi-stage Dockerfile for build verification only (no production tuning yet)
# Stage 1: PHP dependencies (composer install --no-dev for lean runtime later)
FROM composer:2 AS vendor
WORKDIR /app
# Copy composer manifests first (better caching) then application code needed for autoload (some packages require class discovery)
COPY composer.json composer.lock* ./
RUN if [ -f composer.lock ]; then echo "Using existing lock"; else echo "No lock file present, generating"; composer update --no-interaction --no-progress; fi
# Copy rest of code (only what composer might need for classmap optimization; exclude vendor to keep layer small)
COPY app app
COPY database database
COPY routes routes
COPY config config
COPY artisan .
# Install prod dependencies only; --no-scripts to avoid running Laravel post-autoload scripts in this minimal stage
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --no-scripts \
    && composer dump-autoload --optimize

# Stage 2: Node build (if we later want assets) – kept very small and optional
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci --omit=dev || true
# Commented out build step for now (no Vite production build in verification)
# COPY resources/ resources/
# RUN npm run build

# Stage 3: Runtime image
FROM php:8.2-fpm-alpine AS runtime
WORKDIR /var/www/html

# System packages required by common Laravel extensions
RUN apk add --no-cache bash icu-dev oniguruma-dev libzip-dev zip unzip curl git sqlite

# PHP extensions (separate build deps layer for speed)
RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite intl zip || (apk add --no-cache --virtual .build-deps $PHPIZE_DEPS && docker-php-ext-install pdo pdo_mysql pdo_sqlite intl zip && apk del .build-deps)

# Copy application source
COPY . .

# Copy vendor deps from first stage
COPY --from=vendor /app/vendor ./vendor
COPY --from=vendor /app/composer.json ./
COPY --from=vendor /app/composer.lock* ./

# (Optional) copy built assets if enabled later
# COPY --from=frontend /app/dist ./public/build

# Set reasonable defaults
ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr

# Health check placeholder (adjust later)
HEALTHCHECK CMD ["php","-v"]

CMD ["php-fpm"]
