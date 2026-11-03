-include .env
export

# setup for docker-compose-ci build directory
# delete "build" directory to update docker-compose-ci

ifeq (,$(wildcard ./build/))
    $(shell git submodule update --init --remote)
endif

EXTENSION=SemanticExtraSpecialProperties

# docker images
MW_VERSION?=1.43
PHP_VERSION?=8.2
DB_TYPE?=mysql
DB_IMAGE?="mariadb:10"

# extensions
SMW_VERSION?=7.4.0

# composer
# Enables "composer update" inside of extension
COMPOSER_EXT?=true

# nodejs
# Enables node.js related tests and "npm install"
# NODE_JS?=true

# check for build dir and git submodule init if it does not exist
include build/Makefile

.PHONY: composer-phan
composer-phan: .init
	$(compose-exec-wiki) bash -c "cd $(EXTENSION_FOLDER) && composer phan $(COMPOSER_PARAMS)"

# Re-generate the Phan baseline. Phan writes its baseline with 4-space indentation, which MediaWiki
# PHPCS rejects, so convert it to tabs afterwards. The baseline is written inside the container and
# copied back to the working tree.
.PHONY: composer-phan-update-baseline
composer-phan-update-baseline: .init
	$(compose-exec-wiki) bash -c "cd $(EXTENSION_FOLDER) && composer phan -- --save-baseline=.phan/baseline.php || true"
	$(compose) cp wiki:$(EXTENSION_FOLDER)/.phan/baseline.php .phan/baseline.php
	unexpand --first-only -t 4 .phan/baseline.php > /tmp/baseline.php && mv /tmp/baseline.php .phan/baseline.php
