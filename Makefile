tests:
	@vendor/bin/phpunit -c test

phpcs:
	@vendor/bin/phpcs --standard=PSR2 src test

.PHONY: tests phpcs
