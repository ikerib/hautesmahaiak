#!/bin/bash

UID = $(shell id -u)
DOCKER_APP = hautesmahaiak

help: ## Show this help message
	@echo 'usage: make [target]'
	@echo
	@echo 'targets:'
	@egrep '^(.+)\:\ ##\ (.+)' ${MAKEFILE_LIST} | column -t -c 2 -s ':#'

start: ## Start the containers
	UID=${UID} docker compose up -d

stop: ## Stop the containers
	UID=${UID} docker compose stop

restart: ## Restart the containers
	$(MAKE) stop && $(MAKE) start

build: ## Rebuilds all the containers
	UID=${UID} docker compose build

run: ## starts the Symfony development server in detached mode
	UID=${UID} docker compose up -d

logs: ## Show Symfony logs in real time
	UID=${UID} docker compose logs -f

composer-install: ## Installs composer dependencies
	UID=${UID} docker exec --user ${UID} ${DOCKER_APP} composer install --no-interaction
# End backend commands

ssh: ## bash into the be container
	UID=${UID} docker exec -it --user ${UID} ${DOCKER_APP} bash
