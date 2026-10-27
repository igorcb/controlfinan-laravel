# Development image: PHP 8.4 CLI with Postgres, Composer and the Tailwind standalone CLI.
FROM php:8.4-cli

RUN apt-get update && apt-get install -y --no-install-recommends libpq-dev libzip-dev unzip git curl \
    && docker-php-ext-install pdo_pgsql bcmath zip \
    && rm -rf /var/lib/apt/lists/* \
    && curl -fsSL -o /usr/local/bin/tailwindcss \
       https://github.com/tailwindlabs/tailwindcss/releases/download/v4.3.3/tailwindcss-linux-x64 \
    && chmod +x /usr/local/bin/tailwindcss
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

ARG UID=1000
ARG GID=1000
RUN groupadd -g ${GID} app && useradd -u ${UID} -g ${GID} -m app
USER app
WORKDIR /app
EXPOSE 8000
CMD ["bin/dev"]
