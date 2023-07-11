-include .env
export

# ======== Naming ========
EXTENSION := SemanticExtraSpecialProperties
EXTENSION_FOLDER := /var/www/html/extensions/${EXTENSION}
extension := $(shell echo $(EXTENSION) | tr A-Z a-z})
IMAGE_NAME := $(extension):test-$(MW_VERSION)-$(SMW_VERSION)-$(AR_VERSION)-$(PHP_VERSION)


# ======== CI ENV Variables ========
MW_VERSION ?= 1.35
SMW_VERSION ?= 4.1.1
AR_VERSION ?= 1.8.2
PHP_VERSION ?= 7.4
DB_TYPE ?= sqlite
DB_IMAGE ?= ""

environment = IMAGE_NAME=$(IMAGE_NAME) \
MW_VERSION=$(MW_VERSION)  \
SMW_VERSION=$(SMW_VERSION) \
AR_VERSION=$(AR_VERSION) \
PHP_VERSION=$(PHP_VERSION) \
DB_TYPE=$(DB_TYPE) \
DB_IMAGE=$(DB_IMAGE) \
EXTENSION_FOLDER=$(EXTENSION_FOLDER)

ifneq (,$(wildcard ./docker-compose.override.yml))
     COMPOSE_OVERRIDE=-f docker-compose.override.yml
endif

compose = $(environment) docker-compose $(COMPOSE_OVERRIDE) $(COMPOSE_ARGS)
compose-ci = $(environment) docker-compose -f docker-compose.yml -f docker-compose-ci.yml $(COMPOSE_OVERRIDE) $(COMPOSE_ARGS)
compose-dev = $(environment) docker-compose -f docker-compose.yml -f docker-compose-dev.yml $(COMPOSE_OVERRIDE) $(COMPOSE_ARGS)

compose-run = $(compose) run -T --rm
compose-exec-wiki = $(compose) exec -T wiki

show-current-target = @echo; echo "======= $@ ========"


# ======== CI ========
# ======== Global Targets ========

.PHONY: ci
ci: install composer-test

.PHONY: ci-coverage
ci-coverage: install composer-test-coverage

.PHONY: install
install: destroy up .install

.PHONY: up
up: .init .build .up

.PHONY: down
down: .init .down

.PHONY: destroy
destroy: .init .destroy

.PHONY: bash
bash: up .bash

.PHONY: show-logs
show-logs: .init
	$(show-current-target)
	$(compose) logs -f || true

# ======== General Docker-Compose Helper Targets ========
.PHONY: .build
.build:
	$(show-current-target)
	$(compose-ci) build wiki

.PHONY: .up
.up:
	$(show-current-target)
	$(compose-ci) up -d

.PHONY: .install
.install: .wait-for-db
	$(show-current-target)
	$(compose-exec-wiki) bash -c "sudo -u www-data \
		php maintenance/install.php \
		    --pass=wiki4everyone --server=http://localhost:8080 --scriptpath='' \
    		--dbname=wiki --dbuser=wiki --dbpass=wiki $(WIKI_DB_CONFIG) wiki WikiSysop && \
		cat __setup_extension__ >> LocalSettings.php && \
		sudo -u www-data php maintenance/update.php --skip-external-dependencies --quick \
		"

.PHONY: .down
.down:
	$(show-current-target)
	$(compose-ci) down

.PHONY: .destroy
.destroy:
	$(show-current-target)
	$(compose-ci) down -v

.PHONY: .bash
.bash: .init
	$(show-current-target)
	$(compose-exec-wiki) bash -c "cd $(EXTENSION_FOLDER) && bash"

# ======== Test Targets ========

.PHONY: composer-test
composer-test:
	$(show-current-target)
	$(compose-exec-wiki) bash -c "cd $(EXTENSION_FOLDER) && composer test"

.PHONY: composer-test-coverage
composer-test-coverage:
	$(show-current-target)
	$(compose-exec-wiki) bash -c "cd $(EXTENSION_FOLDER) && composer test-coverage"

# ======== Dev Targets ========

.PHONY: dev-bash
dev-bash: .init
	$(compose-dev) run -it wiki bash -c 'service apache2 start && bash'

.PHONY: run
run:
	$(compose-dev) -f docker-compose-dev.yml run -it wiki

# ======== Helpers ========
.PHONY: .init
.init:
	$(show-current-target)
	$(eval COMPOSE_ARGS = --project-name ${extension}-$(DB_TYPE) --profile $(DB_TYPE))
ifeq ($(DB_TYPE), sqlite)
	$(eval WIKI_DB_CONFIG = --dbtype=$(DB_TYPE) --dbpath=/tmp/sqlite)
else
	$(eval WIKI_DB_CONFIG = --dbtype=$(DB_TYPE) --dbserver=$(DB_TYPE) --installdbuser=root --installdbpass=database)
endif
	@echo "COMPOSE_ARGS: $(COMPOSE_ARGS)"

.PHONY: .wait-for-db
.wait-for-db:
	$(show-current-target)
ifeq ($(DB_TYPE), mysql)
	$(compose-run) wait-for $(DB_TYPE):3306 -t 120
else ifeq ($(DB_TYPE), postgres)
	$(compose-run) wait-for $(DB_TYPE):5432 -t 120
endif