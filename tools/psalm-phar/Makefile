COMPOSER_BIN ?= $(shell which composer)
PHP_BIN ?= $(shell which php)

# -----------------------------------------------------------------------------
# Real targets
# -----------------------------------------------------------------------------

composer.lock: composer.json
	${PHP_BIN} ${COMPOSER_BIN} update;
	touch composer.lock;

vendor: composer.lock
	${PHP_BIN} ${COMPOSER_BIN} install --no-interaction --prefer-dist --optimize-autoloader;
	touch vendor;

# -----------------------------------------------------------------------------
# Phony targets
# -----------------------------------------------------------------------------

.PHONY: clean
clean:
	rm -rf ./vendor
