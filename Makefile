
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