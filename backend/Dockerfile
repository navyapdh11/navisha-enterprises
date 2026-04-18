FROM dunglas/frankenphp:latest-php8.3
WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN php artisan storage:link 2>/dev/null || true
ENV SERVER_NAME=:8000
EXPOSE 8000
HEALTHCHECK --interval=10s --timeout=5s --retries=5 CMD curl -f http://localhost:8000/api/health || exit 1
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
