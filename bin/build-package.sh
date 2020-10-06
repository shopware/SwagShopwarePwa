#!/bin/bash

# Run this command from plugin root

rm -rf build && mkdir build && mkdir build/SwagShopwarePwa

cp composer-package.json build/SwagShopwarePwa/composer.json && cp CHANGELOG.md build/SwagShopwarePwa && cp -r src build/SwagShopwarePwa/src

rm -rf build/SwagShopwarePwa/src/Test

cd build/SwagShopwarePwa

composer install --no-dev -n

cp ../../composer.json .

cd ..

zip -q -r SwagShopwarePwa.zip SwagShopwarePwa

echo "Package Build Finished"
