#! /bin/bash

set -x

originalDirectory=$(pwd)

function installMediaWiki {
	cd ..

	wget https://github.com/wikimedia/mediawiki-core/archive/$MW.tar.gz
    tar -zxf $MW.tar.gz
    mv mediawiki-core-$MW phase3

    cd phase3

    mysql -e 'create database its_a_mw;'
    php maintenance/install.php --dbtype $DBTYPE --dbuser root --dbname its_a_mw --dbpath $(pwd) --pass nyan TravisWiki admin
}

function installSemanticExtraSpecialPropertiesAsExtension {
	cd extensions

	cp -r $originalDirectory SemanticExtraSpecialProperties

	cd SemanticExtraSpecialProperties
	composer update --prefer-source

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
