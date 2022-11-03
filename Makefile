# Default port to start server on
PORT := 8888

.PHONY: clean
clean:
	rm -rf ./vendor

.PHONY: install
install:
	composer install --prefer-dist

.PHONY: start-server
start-server:
	composer install --prefer-dist
	php -S localhost:$(PORT) -t public/ public/index.php

# linting based on https://github.com/dbfx/github-phplint
.PHONY: lint
lint:
	# Make sure we don't lint test classes
	composer install --prefer-dist --no-dev

	# Lint for installed PHP version
	sh -c "! (find . -type f -name \"*.php\" -not -path \"./build/*\" -not -path \"./vendor/*\" $1 -exec php -l -n {} \; | grep -v \"No syntax errors detected\")"

	# Make devtools available again
	composer install --prefer-dist

	# Lint with CodeSniffer
	vendor/bin/phpcs --standard=phpcs.xml src/

.PHONY: api_test
api_test:
	newman run test/postman/scim-opf-pfa.postman_collection.json -e test/postman/scim-env.postman_environment.json

.PHONY: fulltest
fulltest: lint api_test