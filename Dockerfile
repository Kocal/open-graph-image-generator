#syntax=docker/dockerfile:1

# Versions
FROM dunglas/frankenphp:1-php8.5.12 AS frankenphp_upstream

# The different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/develop/develop-images/multistage-build/#stop-at-a-specific-build-stage
# https://docs.docker.com/compose/compose-file/#target


# Base FrankenPHP image
FROM frankenphp_upstream AS frankenphp_base

SHELL ["/bin/bash", "-euxo", "pipefail", "-c"]

WORKDIR /app

# persistent deps
# hadolint ignore=DL3008
RUN <<-EOF
	apt-get update
	apt-get install -y --no-install-recommends \
		file \
		git
	install-php-extensions \
		@composer \
		apcu \
		intl \
		opcache \
		zip \
		imagick
	rm -rf /var/lib/apt/lists/*
EOF

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

ENV PHP_INI_SCAN_DIR=":$PHP_INI_DIR/app.conf.d"

###> recipes ###
###< recipes ###

COPY --link frankenphp/conf.d/10-app.ini $PHP_INI_DIR/app.conf.d/
COPY --link --chmod=755 frankenphp/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
COPY --link frankenphp/Caddyfile /etc/frankenphp/Caddyfile

ENTRYPOINT ["docker-entrypoint"]

HEALTHCHECK --start-period=60s CMD php -r 'exit(false === @file_get_contents("http://localhost:2019/metrics", context: stream_context_create(["http" => ["timeout" => 5]])) ? 1 : 0);'
CMD [ "frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile" ]

# TODO: We use Symfony CLI and local PHP for development, but I may be reconsidering this approach in the future
## Dev FrankenPHP image
#FROM frankenphp_base AS frankenphp_dev
#
#ENV APP_ENV=dev
#ENV XDEBUG_MODE=off
#ENV FRANKENPHP_WORKER_CONFIG=watch
#
## dev dependencies
## hadolint ignore=DL3008
#RUN <<-EOF
#	mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
#	apt-get update
#	apt-get install -y --no-install-recommends \
#		aggregate \
#		curl \
#		dnsmasq \
#		dnsutils \
#		iproute2 \
#		ipset \
#		iptables \
#		jq \
#		sudo
#	install-php-extensions xdebug
#	rm -rf /var/lib/apt/lists/*
#	useradd -m -s /bin/bash nonroot
#	echo "nonroot ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/nonroot
#	git config --system --add safe.directory /app
#EOF
#
#COPY --link frankenphp/conf.d/20-app.dev.ini $PHP_INI_DIR/app.conf.d/
#
#CMD [ "frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile", "--watch" ]

# Prod FrankenPHP image
FROM frankenphp_base AS frankenphp_prod

ENV APP_ENV=prod

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --link frankenphp/conf.d/20-app.prod.ini $PHP_INI_DIR/app.conf.d/

# prevent the reinstallation of vendors at every changes in the source code
COPY --link composer.* symfony.* ./
RUN composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

# copy sources
COPY --link --exclude=frankenphp/ . ./

RUN <<-EOF
	mkdir -p var/cache var/log
	composer dump-autoload --classmap-authoritative --no-dev
	composer dump-env prod
	composer run-script --no-dev post-install-cmd
	chmod +x bin/console
	php bin/console asset-map:compile
	sync
EOF
