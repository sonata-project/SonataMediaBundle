#!/usr/bin/env sh
set -ev

PHP_INI_DIR="$HOME/.phpenv/versions/$(phpenv version-name)/etc/conf.d/"
TRAVIS_INI_FILE="$PHP_INI_DIR/travis.ini"
echo "memory_limit=3072M" >> "$TRAVIS_INI_FILE"


if [ "$TRAVIS_PHP_VERSION" '<' '7.0' ]; then
    echo "extension=mongo.so" >> "$TRAVIS_INI_FILE"
else
    echo "extension=mongodb.so" >> "$TRAVIS_INI_FILE"

    # Backwards compatibility with old mongo extension
    composer require "alcaeus/mongo-php-adapter" --ignore-platform-reqs
fi

sed --in-place "s/\"dev-master\":/\"dev-${TRAVIS_COMMIT}\":/" composer.json

if [ "$SYMFONY" != "" ]; then composer require "symfony/symfony:$SYMFONY" --no-update; fi;
if [ "$DOCTRINE_ODM" != "" ]; then composer require "doctrine/mongodb-odm:$DOCTRINE_ODM" --no-update; fi;
if [ "$SONATA_CORE" != "" ]; then composer require "sonata-project/core-bundle:$SONATA_CORE" --no-update; fi;
if [ "$SONATA_ADMIN" != "" ]; then composer require "sonata-project/admin-bundle:$SONATA_ADMIN" --no-update; fi;
