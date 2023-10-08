ifndef u
u:=root
endif

ifndef env
env:=dev
endif

OS:=$(shell uname)

docker-start:
	docker-compose up -d

docker-restart:
	docker-compose down
	make docker-start
	make docker-init-db-full
	make docker-link-storage

create-env:
	cp .env.example .env
	sed -i -e "s/DB_HOST=db/DB_HOST=127.0.0.1/g" .env
	sed -i -e "s/REDIS_HOST=redis/REDIS_HOST=127.0.0.1/g" .env

docker-connect:
	docker exec -it amss_inventory bash

init-app:
	cp .env.example .env
	composer install
	php artisan key:generate
	php artisan passport:keys
	php artisan migrate
	php artisan db:seed
	php artisan storage:link
	rm -rf node_modules
	npm install

docker-init:
	docker exec -it amss_inventory make init-app

migrate:
	docker exec -it amss_inventory php artisan migrate

init-db-full:
	make autoload
	php artisan migrate:fresh
	php artisan db:seed

docker-init-db-full:
	docker exec -it amss_inventory make init-db-full

docker-link-storage:
	docker exec -it amss_inventory php artisan storage:link

init-db:
	make autoload
	php artisan migrate:fresh

start:
	php artisan serve

log:
	tail -f storage/logs/laravel.log

log-daily:
	tail -f "./storage/logs/laravel-$(shell date +"%Y-%m-%d").log"

log-echo:
	docker logs -f laraveldocker_echo-server_1

test-js:
	npm run lint

test-php:
	vendor/bin/phpcs --standard=phpcs.xml && vendor/bin/phpmd app text phpmd.xml

build:
	npm run dev

docker-build:
	docker exec -it amss_inventory make build

watch:
	docker exec -it amss_inventory npm run watch

docker-watch-poll:
	docker exec -it amss_inventory npm run watch-poll

autoload:
	composer dump-autoload

cache:
	php artisan cache:clear && php artisan view:clear

docker-cache:
	docker exec amss_inventory make cache

route:
	php artisan route:list

create-table:
	# Ex: make create-alter n=create_users_table t=users
	docker exec -it amss_inventory php artisan make:migration $(n) --create=$(t)

model:
	php artisan make:model Models/$(n) -m

create-model:
	# Ex: make create-model n=Test
	# Result: app/Models/Test.php
	#         database/migrations/2018_01_05_102531_create_tests_table.php
	docker exec -it amss_inventory php artisan make:model Models/$(n) -m

create-alter:
	# Ex: make create-alter n=add_secret_to_oauth_access_tokens_table t=oauth_access_tokens
	docker exec -it amss_inventory php artisan make:migration $(n) --table=$(t)

deploy:
	ssh $(u)@$(h) "mkdir -p $(dir)"
	rsync -avhzL --delete \
				--no-perms --no-owner --no-group \
				--exclude .git \
				--exclude .idea \
				--exclude .env.example \
				--exclude .env \
				--exclude laravel-echo-server.json \
				--exclude node_modules \
				--exclude bootstrap/cache \
				--exclude public/storage \
				--exclude storage/framework \
				--exclude storage/logs \
				--exclude storage/framework \
				--exclude storage/app \
				. $(u)@$(h):$(dir)/
	ssh $(u)@$(h) "chown www-data:www-data -R /var/www/inventory-drk-backend/storage"

deploy-dev:
	make deploy h=52.76.64.206 dir=/var/www/inventory-drk-backend

swagger:
	docker exec -it amss_inventory php artisan l5-swagger:generate
