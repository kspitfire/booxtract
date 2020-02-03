
fixer:
	bin/php-cs-fixer fix --dry-run --diff
.PHONY: fixer

phpmd:
	phpmd src/ text phpmd.xml
.PHONY: phpmd

phpstan:
	bin/phpstan analyse -l 5 -c phpstan.neon src/
.PHONY: phpstan