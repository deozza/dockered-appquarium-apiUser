# dockered-apiUser

## Introduction

`dockered-apiUser` is a standalone microservice. It is used to handle all the users related features of the Appquarium application.

It is based on [Symfony 5.1](https://symfony.com/doc/current/index.html) and use [Api Platform](https://api-platform.com/docs/).

## Table of content

## Initialize dev environment

__To initialize a dev environment, you need to have `docker`, `docker-compose` and `git` configured__

 * 1 Install repository :

```bash
git clone https://github.com/deozza/dockered-appquarium-apiuser.git
```

 * 2 Build docker container :

```bash
cd dockered-appquarium-apiuser
docker-compose -f docker-compose.dev.yml up -d --build
```

 * 3 Install dependencies :

```bash
docker-compose -f docker-compose.dev.yml composer install
```

 * 4 Setup test and development database :

```bash
docker-compose -f docker-compose.dev.yml bin/console d:m:s:c
docker-compose -f docker-compose.dev.yml bin/console t:f:l
```

 * 5 Launch dev web server :

```bash
docker-compose -f docker-compose.dev.yml exec appquarium-php-apiuser -S php 0.0.0.0:8001 -t public/
```

## Routes

An OpenApi [file](./doc/openapi.yml) is used to describe the routes.

With the dev environment web server launched, the OpenApi doc is available at the route [/docs](127.0.0.1:8001/docs) .

## Tests

__You must have setup the test database first. See [Initialize dev environment](#initialize-dev-environment) to initialize a dev environment and load the fixtures.__

### What is tested ?

`dockered-apiUser` has API endpoint testing. The user `ApiResource` and `UserController` the `AuthenticatorController` are tested. 

### Launch tests :

```bash
bin/phpunit
```

## How to deploy

