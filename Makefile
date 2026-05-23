up:
	docker-compose up -d

down:
	docker-compose down

build:
	docker-compose build --no-cache

restart:
	docker-compose down && docker-compose up -d

logs:
	docker-compose logs -f

shell:
	docker exec -it gavrcore_app bash

composer-install:
	docker exec -it gavrcore_app composer install

composer-create-project:
	docker run --rm -v $(PWD)/src:/app -w /app composer create-project laravel/laravel .

npm-install:
	docker exec -it gavrcore_app npm install

npm-dev:
	docker exec -it gavrcore_app npm run dev

npm-build:
	docker exec -it gavrcore_app npm run build

migrate:
	docker exec -it gavrcore_app php artisan migrate

migrate-fresh:
	docker exec -it gavrcore_app php artisan migrate:fresh --seed

test:
	docker exec -it gavrcore_app php artisan test

queue:
	docker exec -it gavrcore_app php artisan queue:work