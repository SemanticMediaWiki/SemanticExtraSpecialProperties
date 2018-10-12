# Semantic Extra Special Properties
[![Build Status](https://travis-ci.org/SemanticMediaWiki/SemanticExtraSpecialProperties.svg?branch=master)](https://travis-ci.org/SemanticMediaWiki/SemanticExtraSpecialProperties)
[![Code Coverage](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticExtraSpecialProperties/badges/coverage.png?s=c5563fd91abeb49b37a6ef999198530b6796dd3c)](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticExtraSpecialProperties/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticExtraSpecialProperties/badges/quality-score.png?s=9cc8ce493f63f5c2c22db71b2061b4b8c21f43ba)](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticExtraSpecialProperties/)
[![Latest Stable Version](https://poser.pugx.org/mediawiki/semantic-extra-special-properties/version.png)](https://packagist.org/packages/mediawiki/semantic-extra-special-properties)
[![Packagist download count](https://poser.pugx.org/mediawiki/semantic-extra-special-properties/d/total.png)](https://packagist.org/packages/mediawiki/semantic-extra-special-properties)

Semantic Extra Special Properties (a.k.a. SESP) is an extension to [Semantic MediaWiki][smw] which adds some extra special properties to enabled content pages.

## Requirements

- PHP 5.6 or later
- MediaWiki 1.27 or later
- Semantic MediaWiki 3.0 or later

## Installation

The recommended way to install Semantic Extra Special Properties is using [Composer](http://getcomposer.org) 
with [MediaWiki's built-in support for Composer](https://www.mediawiki.org/wiki/Composer).

Note that the required extension Semantic MediaWiki must be installed first according to the installation
instructions provided.

### Step 1

Change to the base directory of your MediaWiki installation. This is where the "LocalSettings.php"
file is located. If you have not yet installed Composer do it now by running the following command
in your shell:

    wget https://getcomposer.org/composer.phar

### Step 2
    
If you do not have a "composer.local.json" file yet, create one and add the following content to it:

```
{
	"require": {
		"mediawiki/semantic-extra-special-properties": "~2.0"
	}
}
```

If you already have a "composer.local.json" file add the following line to the end of the "require"
section in your file:

    "mediawiki/semantic-extra-special-properties": "~2.0"

Remember to add a comma to the end of the preceding line in this section.

### Step 3

Run the following command in your shell:

    php composer.phar update --no-dev

Note if you have Git installed on your system add the `--prefer-source` flag to the above command. Also
note that it may be necessary to run this command twice. If unsure do it twice right away.

### Step 4

Add the following line to the end of your "LocalSettings.php" file:

    wfLoadExtension( 'SemanticExtraSpecialProperties' );

### Step 5

Add the [configuration parameters](/docs/configuration.md) to the "LocalSettings.php" file according to your
requirements below the call to `wfLoadExtension` added in step 4.

### Step 6

This step may be skipped in case you are freshly intalling this extension. If this extension is being upgraded
from an version earlier than 2.0.0 you have to change your configruation in the "LocalSettings.php" file according
to the consise [migration guide](/docs/migration-to-200.md).

### Step 7

Run the **["update.php"][mw-update]** maintenance script to ensure that property tables are properly
initialized.

### Verify installation success

As final step, you can verify SESP got installed by looking at the "Special:Version" page on your wiki and
check that it is listed in the semantic extensions section.

## Usage

The annotation process for predefined properties is mostly done in the background and therefore does not need
any special interaction from a user but ["LocalSettings.php"][mw-localsettings] for SESP need to be enabled.

For details about available configruation parameters, please have a look at the [configuration](docs/configuration.md)
document.

## Contribution and support

If you have remarks, questions, or suggestions, please send them to semediawiki-users@lists.sourceforge.net. You can subscribe to this list [here](http://sourceforge.net/mailarchive/forum.php?forum_name=semediawiki-user).

If you want to contribute work to the project please subscribe to the developers mailing list and have a look at the [contribution guildline](/CONTRIBUTING.md). A list of people who have made contributions in the past can be found [here][contributors].

* [File an issue](https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties/issues)
* [Submit a pull request](https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties/pulls)
* Ask a question on [the mailing list](https://semantic-mediawiki.org/wiki/Mailing_list)
* Ask a question on the #semantic-mediawiki IRC channel on Freenode.

### Tests

This extension provides unit and integration tests that are run by a [continues integration platform][travis]
but can also be executed using `composer phpunit` from the extension base directory.

## License

[GNU General Public License 2.0 or later][licence]

[composer]: https://getcomposer.org/
[licence]: https://www.gnu.org/copyleft/gpl.html
[mwcomposer]: https://www.mediawiki.org/wiki/Composer
[smw]: https://www.semantic-mediawiki.org/wiki/Semantic_MediaWiki
[travis]: https://travis-ci.org/SemanticMediaWiki/SemanticExtraSpecialProperties
[mw-testing]: https://www.mediawiki.org/wiki/Manual:PHP_unit_testing
[mw-update]: https://www.mediawiki.org/wiki/Manual:Update.php
[mw-localsettings]: https://www.mediawiki.org/wiki/Localsettings
[contributors]: https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties/graphs/contributors
[semver]: https://semver.org/
