DOCKER_BUILD_VARS := COMPOSE_DOCKER_CLI_BUILD=1 DOCKER_BUILDKIT=1
COMPOSE := $(DOCKER_BUILD_VARS) docker-compose

start:
	${COMPOSE} up -d

stop:
	${COMPOSE} down

restart: stop start

destroy: stop
	${COMPOSE} rm --force --stop -v