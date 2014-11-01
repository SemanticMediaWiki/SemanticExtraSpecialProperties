#! /bin/bash

set -x

originalDirectory=$(pwd)

function installMediaWiki {
	cd ..

	wget https://github.com/wikimedia/mediawiki/archive/$MW.tar.gz
	tar -zxf $MW.tar.gz
	mv mediawiki-$MW phase3

	cd phase3

	## MW 1.25 requires Psr\Logger
	if [ "$MW" == "master" ]
	then
	  composer install
	fi

	mysql -e 'create database its_a_mw;'
	php maintenance/install.php --dbtype $DBTYPE --dbuser root --dbname its_a_mw --dbpath $(pwd) --pass nyan TravisWiki admin
}

function installSemanticExtraSpecialPropertiesAsExtension {
	cd extensions

	cp -r $originalDirectory SemanticExtraSpecialProperties

	cd SemanticExtraSpecialProperties

	composer require 'phpunit/phpunit=3.7.*' --prefer-source --update-with-dependencies
	composer update --prefer-source --dev

	cd ../..

	echo 'require_once( __DIR__ . "/extensions/SemanticExtraSpecialProperties/SemanticExtraSpecialProperties.php" );' >> LocalSettings.php

	echo 'error_reporting(E_ALL| E_STRICT);' >> LocalSettings.php
	echo 'ini_set("display_errors", 1);' >> LocalSettings.php
	echo '$wgShowExceptionDetails = true;' >> LocalSettings.php
	echo '$wgDevelopmentWarnings = true;' >> LocalSettings.php
	echo "putenv( 'MW_INSTALL_PATH=$(pwd)' );" >> LocalSettings.php

	php maintenance/update.php --quick

	cd tests/phpunit

	if [ "$TYPE" == "coverage" ]
	then
		php phpunit.php --group SESPExtension -c ../../extensions/SemanticExtraSpecialProperties/phpunit.xml.dist --coverage-clover $originalDirectory/build/coverage.clover
	else
		php phpunit.php --group SESPExtension -c ../../extensions/SemanticExtraSpecialProperties/phpunit.xml.dist
	fi
}

installMediaWiki
installSemanticExtraSpecialPropertiesAsExtension
