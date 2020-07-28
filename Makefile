include .env
export

install:
	cp .env.dist .env
	docker-compose up
.PHONY: install

update:
	docker exec composer composer update
.PHONY: update

fixer:
	docker exec composer /app/bin/php-cs-fixer fix --dry-run --diff
.PHONY: fixer

fix:
	docker exec composer /app/bin/php-cs-fixer fix
.PHONY: fix

phpmd:
	docker exec composer /app/phpmd src/ text phpmd.xml
.PHONY: phpmd

phpstan:
	docker exec composer /app/bin/phpstan analyse -l 5 -c phpstan.neon src/
.PHONY: phpstan

process:
	docker exec composer /app/bin/console books:process -p $(BOOK_DIR)
.PHONY: process

manual:
	docker exec composer /app/bin/console books:process -m -v -p $(BOOK_DIR)
.PHONY: manual

dry-run:
	docker exec composer /app/bin/console books:process -d -v -p $(BOOK_DIR)
.PHONY: dry-run

analyze:
	docker exec composer /app/bin/console books:process -d -vv -p $(BOOK_DIR)
.PHONY: analyze