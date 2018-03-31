#!/bin/bash
set -ex

BASE_PATH=$(pwd)
MW_INSTALL_PATH=$BASE_PATH/../mw

# Run Composer installation from the MW root directory
function installToMediaWikiRoot {
	echo -e "Running MW root composer install build on $TRAVIS_BRANCH \n"

	cd $MW_INSTALL_PATH

	if [ "$PHPUNIT" != "" ]
	then
		composer require 'phpunit/phpunit='$PHPUNIT --prefer-source --update-with-dependencies
	else
		composer require 'phpunit/phpunit=3.7.*' --prefer-source --update-with-dependencies
	fi

	if [ "$SESP" != "" ]
	then
		composer require 'mediawiki/semantic-extra-special-properties='$SESP --prefer-source --update-with-dependencies
	else
		composer init --stability dev
		composer require mediawiki/semantic-extra-special-properties "dev-master" --prefer-source --dev --update-with-dependencies

		cd extensions
		cd SemanticExtraSpecialProperties

		# Pull request number, "false" if it's not a pull request
		# After the install via composer an additional get fetch is carried out to
		# update th repository to make sure that the latests code changes are
		# deployed for testing
		if [ "$TRAVIS_PULL_REQUEST" != "false" ]
		then
			git fetch origin +refs/pull/"$TRAVIS_PULL_REQUEST"/merge:
			git checkout -qf FETCH_HEAD
		else
			git fetch origin "$TRAVIS_BRANCH"
			git checkout -qf FETCH_HEAD
		fi

		cd ../..
	fi

	# Rebuild the class map for added classes during git fetch
	composer dump-autoload
}

function updateConfiguration {

	cd $MW_INSTALL_PATH

	# Site language
	if [ "$SITELANG" != "" ]
	then
		echo '$wgLanguageCode = "'$SITELANG'";' >> LocalSettings.php
	fi

	echo 'error_reporting(E_ALL| E_STRICT);' >> LocalSettings.php
	echo 'ini_set("display_errors", 1);' >> LocalSettings.php
	echo '$wgShowExceptionDetails = true;' >> LocalSettings.php
	echo '$wgDevelopmentWarnings = true;' >> LocalSettings.php
	echo "putenv( 'MW_INSTALL_PATH=$(pwd)' );" >> LocalSettings.php

	# SMW#1732
	echo 'wfLoadExtension( "SemanticMediaWiki" );' >> LocalSettings.php

	# SRF#103
	echo 'wfLoadExtension( "SemanticExtraSpecialProperties" );' >> LocalSettings.php

	php maintenance/update.php --quick
}

installToMediaWikiRoot
updateConfiguration
