EXTENSION := SemanticExtraSpecialProperties

MW_VERSION ?= 1.35
SMW_VERSION ?= 4.1.1
AR_VERSION ?= 1.8.2

IMAGE_VERSION := $(MW_VERSION)-$(SMW_VERSION)-$(AR_VERSION)
BUILD_ARGS := \
	--build-arg MW_VERSION=$(MW_VERSION) \
	--build-arg SMW_VERSION=$(SMW_VERSION) \
	--build-arg AR_VERSION=$(AR_VERSION)

# -------------------------------------------------------------------
DB_TYPE ?= sqlite
extension := $(shell echo $(EXTENSION) | tr A-Z a-z})
IMAGE_NAME := $(extension):test-$(IMAGE_VERSION)
EXTENSION_FOLDER := /var/www/html/extensions/${EXTENSION}

compose = IMAGE_NAME=$(IMAGE_NAME) EXTENSION_FOLDER=$(EXTENSION_FOLDER) docker-compose $(COMPOSE_ARGS)
compose-run = $(compose) run -T --rm
compose-exec-wiki = $(compose) exec -T wiki

show-current-target = @echo; echo "======= $@ ========"

.PHONY: ci
ci: install
	$(show-current-target)
	$(compose-exec-wiki) bash -c "cd $(EXTENSION_FOLDER) && composer test"

.PHONY: ci-coverage
ci-coverage: install
	$(show-current-target)
	$(compose-exec-wiki) bash -c "cd $(EXTENSION_FOLDER) && composer test-coverage"

.PHONY: install
install: destroy up .install

.PHONY: up
up: .init .build .up

.PHONY: down
down: .init .down

.PHONY: destroy
destroy: .init .destroy

.PHONY: bash
bash: .init
	$(show-current-target)
	$(compose) exec wiki bash -c "cd $(EXTENSION_FOLDER) && bash"

.PHONY: show-logs
show-logs: .init
	$(show-current-target)
	$(compose) logs -f || true

.PHONY: .build
.build:
	$(show-current-target)
	$(compose) build $(BUILD_ARGS) wiki

.PHONY: .up
.up:
	$(show-current-target)
	$(compose) up -d

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
	$(compose) down

.PHONY: .destroy
.destroy:
	$(show-current-target)
	$(compose) down -v

.PHONY: .wait-for-db
.wait-for-db:
	$(show-current-target)
ifneq ($(DB_TYPE), sqlite)
	$(compose-run) wait-for $(DB_TYPE):3306 -t 120
endif

.PHONY: .init
.init:
	$(show-current-target)
ifeq ($(DB_TYPE), mysql)
	$(eval COMPOSE_ARGS = --project-name $(extension)-mysql --profile mysql)
	$(eval WIKI_DB_CONFIG = --dbtype=mysql --dbserver=mysql --installdbuser=root --installdbpass=database)
else
	$(eval COMPOSE_ARGS = --project-name $(extension)-sqlite)
	$(eval WIKI_DB_CONFIG = --dbtype=sqlite --dbpath=/data/sqlite)
endif
	@echo "COMPOSE_ARGS: $(COMPOSE_ARGS)"
