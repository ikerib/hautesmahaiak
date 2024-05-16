#!/usr/bin/env bash

RED="\033[0;31m"

echo ""
echo -e "${RED}##############################################################"
echo "# Ez ezeztatu eragiketa mesedez." >&2;
echo -e "${RED}##############################################################"
echo ""
php bin/console doctrine:schema:update --force

php bin/console importmap:install

php bin/console tailwind:build

php bin/console asset-map:compile

php bin/console cache:clear

chown -R www-data:www-data var/

echo ""
echo -e "${RED}##############################################################"
echo "# Prozesua amaitu da." >&2;
echo -e "${RED}##############################################################"
echo ""
