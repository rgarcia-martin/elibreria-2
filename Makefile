SHELL := /bin/bash
.ONESHELL:

## ---------- Config ----------
COMPOSE    ?= docker compose
PHP_SVC    ?= php
NGINX_SVC  ?= nginx
DB_SVC     ?= mysql
PHP_EXEC   ?= $(COMPOSE) exec -e XDEBUG_MODE=off $(PHP_SVC)

## ---------- Ayuda ----------
.PHONY: help
help: ## Muestra esta ayuda
	@awk 'BEGIN {print "Targets disponibles:\n"} /^[a-zA-Z0-9_.-]+:.*##/ {\
	  split($$0, a, ":"); \
	  split($$0, b, "##"); \
	  printf "  \033[36m%-24s\033[0m %s\n", a[1], b[2] \
	}' $(MAKEFILE_LIST)

## ---------- Docker ----------
.PHONY: up down build restart logs sh-php sh-nginx sh-db
up: ## Levanta el stack
	$(COMPOSE) up -d

down: ## Tira el stack y volúmenes
	$(COMPOSE) down -v

build: ## Build sin caché y pull
	$(COMPOSE) build --pull --no-cache

restart: ## Reinicia con rebuild
	$(COMPOSE) down
	$(COMPOSE) up -d --build

logs: ## Logs tail -f
	$(COMPOSE) logs -f --tail=200

sh-php: ## Shell dentro del contenedor PHP
	$(COMPOSE) exec $(PHP_SVC) bash -lc "php -v && bash"

rsh-php: ## Shell dentro del contenedor PHP
	$(COMPOSE) exec -u 0 $(PHP_SVC) bash -lc "php -v && bash"

sh-nginx: ## Shell dentro del contenedor Nginx
	$(COMPOSE) exec $(NGINX_SVC) sh

sh-db: ## Shell dentro del contenedor DB
	$(COMPOSE) exec $(DB_SVC) bash -lc "mysql -u root -p$$MYSQL_ROOT_PASSWORD || bash"

## ---------- Symfony ----------
.PHONY: sf cache-clear cache-warmup migrate make-migration
# Captura todo lo que escribas tras 'sf' como argumentos (evaluación diferida!)
ARGS = $(filter-out $@,$(MAKECMDGOALS))

# Captura lo que escribas después de 'sf' (evaluación diferida con '=')
ARGS = $(filter-out $@,$(MAKECMDGOALS))

sf: ## Ejecuta bin/console con argumentos: p.ej. `make sf about`
	@$(PHP_EXEC) bin/console $(ARGS)

cache-clear: ## cache:clear
	$(PHP_EXEC) bin/console cache:clear

cache-warmup: ## cache:warmup
	$(PHP_EXEC) bin/console cache:warmup

migrate: ## doctrine:migrations:migrate -n
	$(PHP_EXEC) bin/console doctrine:migrations:migrate -n

make-migration: ## make:migration
	$(PHP_EXEC) bin/console make:migration

## ---------- Composer ----------
.PHONY: composer composer-allow-plugins composer-install composer-update
composer: ## Ejecuta composer dentro del contenedor: `make composer C="require pkg"`
	$(COMPOSE) run --rm $(PHP_SVC) composer $(C)

composer-allow-plugins: ## Permite plugins necesarios (Flex/Runtime)
	$(COMPOSE) exec $(PHP_SVC) bash -lc '\
		composer config --no-plugins allow-plugins.symfony/flex true && \
		composer config --no-plugins allow-plugins.symfony/runtime true \
	'

composer-install: composer-allow-plugins ## Instala deps (vendor temporal → copia a C:)
	$(COMPOSE) exec $(PHP_SVC) bash -lc '\
		set -euo pipefail && \
		rm -rf /tmp/vendor && mkdir -p /tmp/vendor && \
		XDEBUG_MODE=off COMPOSER_VENDOR_DIR=/tmp/vendor composer install --no-interaction --prefer-dist --no-scripts && \
		mkdir -p /var/www/app/vendor && rm -rf /var/www/app/vendor/* && \
		cp -r /tmp/vendor/. /var/www/app/vendor/ && \
		XDEBUG_MODE=off composer symfony:sync-recipes --force --verbose || true && \
		XDEBUG_MODE=off composer dump-autoload --no-interaction && \
		XDEBUG_MODE=off composer run-script auto-scripts --no-interaction || true \
	'

composer-update: composer-allow-plugins ## Actualiza deps (vendor temporal → copia a C:)
	$(COMPOSE) exec $(PHP_SVC) bash -lc '\
		set -euo pipefail && \
		rm -rf /tmp/vendor && mkdir -p /tmp/vendor && \
		XDEBUG_MODE=off COMPOSER_VENDOR_DIR=/tmp/vendor composer update --no-interaction --prefer-dist && \
		mkdir -p /var/www/app/vendor && rm -rf /var/www/app/vendor/* && \
		cp -r /tmp/vendor/. /var/www/app/vendor/ && \
		XDEBUG_MODE=off composer dump-autoload --no-interaction \
	'

## ---------- Tests / QA ----------
.PHONY: test
test: ## Lanza PHPUnit
	$(COMPOSE) exec $(PHP_SVC) bash -lc 'XDEBUG_MODE=off ./vendor/bin/phpunit'

## ---------- Fallback para que `make sf about` no falle por target extra ----------
%:
	@:
