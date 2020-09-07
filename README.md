# Appquarium user api

## Introduction

## Table of content

## Initialize dev environment

__To initialize a dev environment, you need to have `docker`, `docker-compose` and `git` configured__

`
//Install repository :

git clone https://github.com/deozza/dockered-appquarium-apiuser.git

//Build docker container :

cd dockered-appquarium-apiuser

docker-compose -f docker-compose.dev.yml up -d --build

//Instal dependencies :

docker-compose -f docker-compose.dev.yml composer install

//Setup test database :

docker-compose -f docker-compose.dev.yml bin/console d:m:s:c
docker-compose -f docker-compose.dev.yml bin/console t:f:l

//Launch web server
docker-compose -f docker-compose.dev.yml exec appquarium-php-apiuser -S php 0.0.0.0:8001 -t public/

`

## Routes



## Tests

## How to deploy
