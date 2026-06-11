-include .env
export

# delete "build" directory to update docker-compose-ci

ifeq (,$(wildcard ./build/))
    $(shell git submodule update --init --remote)
endif

EXTENSION=Arrays

# docker images
MW_VERSION?=1.43
PHP_VERSION?=8.3
DB_TYPE?=mysql
DB_IMAGE?="mysql:8"

# composer
# Enables "composer update" inside of extension
COMPOSER_EXT?=true

# nodejs
# Enables node.js related tests and "npm install"
NODE_JS?=true

include build/Makefile
