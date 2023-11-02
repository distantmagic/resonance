BUILD_ID ?= 20
COMPOSER_BIN ?= $(shell which composer)
DOCS_ESBUILD_TARGET_DIRECTORY ?= docs/build/assets
PHP_BIN ?= $(shell which php)
SHELL_PWD := $(shell pwd)

CSS_SOURCES := $(wildcard resources/css/*.css)

CSS_ENTRYPOINTS := $(wildcard resources/css/docs-*.css)
MD_SOURCES := \
	$(wildcard docs/pages/*.md) \
	$(wildcard docs/pages/*/*.md) \
	$(wildcard docs/pages/*/*/*.md) \
	$(wildcard docs/pages/*/*/*/*.md) \
	$(wildcard docs/pages/*/*/*/*/*.md)
PHP_SOURCES := \
	$(wildcard app/*.php) \
	$(wildcard app/*/*.php)
TS_ENTRYPOINTS := \
	$(wildcard resources/ts/docs/controller_*.ts) \
	$(wildcard resources/ts/docs/global_*.ts)
TS_SOURCES := \
	$(wildcard resources/ts/*.js) \
	$(wildcard resources/ts/*.jsx) \
	$(wildcard resources/ts/*.ts) \
	$(wildcard resources/ts/*.tsx) \
	$(wildcard resources/ts/*/*.js) \
	$(wildcard resources/ts/*/*.jsx) \
	$(wildcard resources/ts/*/*.ts) \
	$(wildcard resources/ts/*/*.tsx) \
	$(wildcard resources/ts/*/*/*.js) \
	$(wildcard resources/ts/*/*/*.jsx) \
	$(wildcard resources/ts/*/*/*.ts) \
	$(wildcard resources/ts/*/*/*.tsx)

# -----------------------------------------------------------------------------
# Real targets
# -----------------------------------------------------------------------------

config.ini: config.ini.example
	cp config.ini.example config.ini;
	sed -i 's/build_id = "A"/build_id = "$(BUILD_ID)"/g' config.ini;

composer.lock: composer.json
	${PHP_BIN} ${COMPOSER_BIN} update;
	touch composer.lock;

docs/artifact.tar: docs/build
	tar \
		--dereference --hard-dereference \
		-cvf "docs/artifact.tar" \
		--exclude=.git \
		--exclude=.github \
		--directory "docs/build" .

docs/build: config.ini esbuild vendor $(MD_SOURCES) $(PHP_SOURCES)
	${PHP_BIN} ./bin/resonance.php static-pages:build;

node_modules: yarn.lock
	yarnpkg install --check-files --frozen-lockfile --non-interactive;
	touch node_modules;

tools/php-cs-fixer/vendor/bin/php-cs-fixer:
	$(MAKE) -C tools/php-cs-fixer vendor

vendor: composer.lock
	${PHP_BIN} ${COMPOSER_BIN} install --no-interaction --prefer-dist --optimize-autoloader;
	touch vendor;

yarn.lock: package.json
	yarnpkg install;
	touch yarn.lock;

esbuild: $(CSS_SOURCES) node_modules
	./node_modules/.bin/esbuild \
		--bundle \
		--asset-names="./[name]_[hash]" \
		--entry-names="./[name]_[hash]" \
		--format=esm \
		--loader:.jpg=file \
		--loader:.otf=file \
		--loader:.svg=file \
		--loader:.ttf=file \
		--metafile=esbuild-meta-docs.json \
		--outdir=$(DOCS_ESBUILD_TARGET_DIRECTORY) \
		--sourcemap \
		--splitting \
		--target=safari16 \
		--tree-shaking=true \
		--tsconfig=tsconfig.json \
		$(CSS_ENTRYPOINTS) \
		$(TS_ENTRYPOINTS) \
	;

# -----------------------------------------------------------------------------
# Phony targets
# -----------------------------------------------------------------------------

.PHONY: build
build: esbuild vendor

.PHONY: clean
clean:
	$(MAKE) -C tools/php-cs-fixer clean
	rm -rf ./.php-cs-fixer.cache
	rm -rf ./.phpunit.cache
	rm -rf ./.phpunit.result.cache
	rm -rf ./coverage
	rm -rf ./esbuild-meta-docs.json
	rm -rf ./docs/build
	rm -rf ./node_modules
	rm -rf ./vendor

.PHONY: eslint
eslint: node_modules
	./node_modules/.bin/eslint resources/ts

.PHONY: eslint.fix
eslint.fix: node_modules
	./node_modules/.bin/eslint --fix resources/ts

.PHONY: eslint.watch
eslint.watch: node_modules
	./node_modules/.bin/nodemon \
		--ext ts,tsx,frag,vert \
		--watch ./resources/ts \
		--exec '$(MAKE) eslint || exit 1'

.PHONY: fmt
fmt: php-cs-fixer prettier

.PHONY: jest
jest: node_modules
	./node_modules/.bin/jest

.PHONY: php-cs-fixer
php-cs-fixer: tools/php-cs-fixer/vendor/bin/php-cs-fixer
	./tools/php-cs-fixer/vendor/bin/php-cs-fixer --allow-risky=yes fix

.PHONY: phpunit
phpunit: config.ini vendor
	./vendor/bin/phpunit

.PHONY: prettier
prettier: node_modules
	./node_modules/.bin/prettier \
		--write \
		"resources/{css,ts}/**/*.{js,css,ts,tsx}"

.PHONY: psalm
psalm: config.ini vendor
	./vendor/bin/psalm --no-cache --show-info=true

.PHONY: psalm.watch
psalm.watch: node_modules vendor
	./node_modules/.bin/nodemon \
		--ext ini,php \
		--signal SIGTERM \
		--watch ./app \
		--watch ./config.schema.php \
		--watch ./constants.php \
		--watch ./resonance \
		--exec '$(MAKE) psalm || exit 1'

.PHONY: ssg
ssg: docs/build

.PHONY: ssg.watch
ssg.watch: node_modules
	./node_modules/.bin/nodemon \
		--ext css,ini,md,php,ts \
		--signal SIGTERM \
		--watch ./app \
		--watch ./docs/pages \
		--watch ./resonance \
		--watch ./resources \
		--exec '$(MAKE) ssg || exit 1'

.PHONY: ssg.serve
ssg.serve: ssg node_modules
	./node_modules/.bin/esbuild --serve=8080 --servedir=docs/build

.PHONY: tsc
tsc: node_modules
	./node_modules/.bin/tsc --noEmit

.PHONY: tsc.watch
tsc.watch: node_modules
	./node_modules/.bin/tsc --noEmit --watch
