$(shell if [ ! -f .env ]; then cp .env.dist .env; echo ".env was successfully created"; fi;)
include .env
export

EXEC=docker run --rm -v ${PWD}:/app -it booxtract
CONSOLE=bin/console

build:
	docker build . -t booxtract
.PHONY: build

check: build
	$(EXEC) composer --version
.PHONY: check

update: build
	$(EXEC) composer update
.PHONY: update

install: build
	$(EXEC) composer install -o --no-interaction
.PHONY: install

cslint: build
	$(EXEC) /app/bin/php-cs-fixer fix --dry-run --diff
.PHONY: cslint

fix: build
	$(EXEC) /app/bin/php-cs-fixer fix
.PHONY: fix

phpmd: build
	$(EXEC) /app/bin/phpmd src/ text phpmd.xml
.PHONY: phpmd

phpstan: build
	$(EXEC) /app/bin/phpstan analyse -l 5 -c phpstan.neon src/
.PHONY: phpstan

process: build
	$(EXEC) $(CONSOLE) books:process -p $(BOOK_DIR)
.PHONY: process

manual: build
	$(EXEC) $(CONSOLE) books:process -m -v -p $(BOOK_DIR)
.PHONY: manual

dry-run: build
	$(EXEC) $(CONSOLE) books:process -d -v -p $(BOOK_DIR)
.PHONY: dry-run

analyze: build
	$(EXEC) $(CONSOLE) books:process -d -vv -p $(BOOK_DIR)
.PHONY: analyze
