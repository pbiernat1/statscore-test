APP_CONTAINER = football_events_app

up:
	docker compose up --build -d

down:
	docker compose down

build:
	docker compose build

restart:
	docker compose restart

logs:
	docker compose logs -f

logs-app:
	docker compose logs -f app

shell:
	docker exec -it $(APP_CONTAINER) sh

composer-install:
	docker exec -it $(APP_CONTAINER) composer install --no-interaction --prefer-dist --optimize-autoloader

composer-update:
	docker exec -it $(APP_CONTAINER) composer update --no-interaction

test: test-unit

test-unit:
	docker exec -it $(APP_CONTAINER) vendor/bin/phpunit tests

test-api:
	docker exec -it $(APP_CONTAINER) vendor/bin/codecept run Api

test-all:
	docker exec -it $(APP_CONTAINER) vendor/bin/codecept run

test-coverage:
	vendor/bin/phpunit
stan:
	vendor/bin/phpstan analyse

clean:
	docker compose down -v --remove-orphans
