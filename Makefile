BUILD_ID ?= 20
COMPOSER_BIN ?= $(shell which composer)
ESBUILD_TARGET_DIRECTORY ?= docs/build/assets
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

.pnp.cjs: yarn.lock
	yarnpkg install --immutable;
	touch .pnp.cjs;

config.ini: config.ini.example
	cp config.ini.example config.ini;

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
	cp resources/images/* docs/build;

tools/php-cs-fixer/vendor/bin/php-cs-fixer:
	$(MAKE) -C tools/php-cs-fixer vendor

tools/psalm/vendor/bin/psalm:
	$(MAKE) -C tools/psalm vendor

vendor: composer.lock
	${PHP_BIN} ${COMPOSER_BIN} install --no-interaction --prefer-dist --optimize-autoloader;
	touch vendor;

yarn.lock: package.json
	yarnpkg install;
	touch yarn.lock;

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
	rm -rf ./.pnp.cjs
	rm -rf ./.pnp.loader.mjs
	rm -rf ./.yarn
	rm -rf ./coverage
	rm -rf ./docs/build
	rm -rf ./esbuild-meta-docs.json
	rm -rf ./vendor

.PHONY: esbuild
esbuild: $(CSS_SOURCES) .pnp.cjs
	yarnpkg run esbuild \
		--bundle \
		--asset-names="./[name]_[hash]" \
		--entry-names="./[name]_[hash]" \
		--format=esm \
		--loader:.jpg=file \
		--loader:.otf=file \
		--loader:.png=file \
		--loader:.svg=file \
		--loader:.ttf=file \
		--loader:.webp=file \
		--metafile=esbuild-meta-docs.json \
		--minify \
		--outdir=$(ESBUILD_TARGET_DIRECTORY) \
		--sourcemap \
		--splitting \
		--target=safari16 \
		--tree-shaking=true \
		--tsconfig=tsconfig.json \
		$(CSS_ENTRYPOINTS) \
		$(TS_ENTRYPOINTS) \
	;

.PHONY: eslint
eslint: .pnp.cjs
	yarnpkg run eslint resources/ts

.PHONY: eslint.fix
eslint.fix: .pnp.cjs
	yarnpkg run eslint --fix resources/ts

.PHONY: eslint.watch
eslint.watch: .pnp.cjs
	yarnpkg run nodemon \
		--ext ts,tsx,frag,vert \
		--watch ./resources/ts \
		--exec '$(MAKE) eslint || exit 1'

.PHONY: fmt
fmt: php-cs-fixer prettier

.PHONY: jest
jest: .pnp.cjs
	yarnpkg run jest

.PHONY: php-cs-fixer
php-cs-fixer: tools/php-cs-fixer/vendor/bin/php-cs-fixer
	./tools/php-cs-fixer/vendor/bin/php-cs-fixer --allow-risky=yes fix

.PHONY: phpunit
phpunit: config.ini vendor
	./vendor/bin/phpunit

.PHONY: prettier
prettier: .pnp.cjs
	yarnpkg run prettier \
		--write \
		"resources/{css,ts}/**/*.{js,css,ts,tsx}"

.PHONY: psalm
psalm: tools/psalm/vendor/bin/psalm vendor
	./tools/psalm/vendor/bin/psalm \
		--no-cache \
		--show-info=true \
		--root=$(CURDIR)

.PHONY: psalm.taint
psalm.taint: .pnp.cjs vendor
	./tools/psalm/vendor/bin/psalm \
		--no-cache \
		--show-info=true \
		--root=$(CURDIR) \
		--taint-analysis

.PHONY: psalm.watch
psalm.watch: .pnp.cjs vendor
	yarnpkg run nodemon \
		--ext ini,php \
		--signal SIGTERM \
		--watch ./app \
		--watch ./config.schema.php \
		--watch ./constants.php \
		--watch ./resonance \
		--watch ./src \
		--exec '$(MAKE) psalm || exit 1'

.PHONY: ssg
ssg: docs/build

.PHONY: ssg.watch
ssg.watch: .pnp.cjs
	yarnpkg run nodemon \
		--ext css,ini,md,php,ts \
		--signal SIGTERM \
		--watch ./app \
		--watch ./docs/pages \
		--watch ./src \
		--watch ./resources \
		--exec '$(MAKE) ssg || exit 1'

.PHONY: ssg.serve
ssg.serve: .pnp.cjs ssg
	yarnpkg run esbuild --serve=8080 --servedir=docs/build

.PHONY: tsc
tsc: .pnp.cjs
	yarnpkg run tsc --noEmit

.PHONY: tsc.watch
tsc.watch: .pnp.cjs
	yarnpkg run tsc --noEmit --watch
