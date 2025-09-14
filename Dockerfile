# Minimal multi-stage Dockerfile for build verification only (no production tuning yet)
# Stage 1: PHP dependencies (composer install --no-dev for lean runtime later)
FROM composer:2 AS vendor
WORKDIR /app
# Copy only composer manifests first for better layer caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress

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

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite intl zip

# Copy application source
COPY . .

# Copy vendor deps from first stage
COPY --from=vendor /app/vendor ./vendor

# (Optional) copy built assets if enabled later
# COPY --from=frontend /app/dist ./public/build

# Set reasonable defaults
ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr

# Health check placeholder (adjust later)
HEALTHCHECK CMD ["php","-v"]

CMD ["php-fpm"]
