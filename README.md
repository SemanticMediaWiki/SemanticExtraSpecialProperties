# Semantic Extra Special Properties
[![Build Status](https://travis-ci.org/SemanticMediaWiki/SemanticExtraSpecialProperties.svg?branch=master)](https://travis-ci.org/SemanticMediaWiki/SemanticExtraSpecialProperties)
[![Code Coverage](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticExtraSpecialProperties/badges/coverage.png?s=c5563fd91abeb49b37a6ef999198530b6796dd3c)](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticExtraSpecialProperties/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticExtraSpecialProperties/badges/quality-score.png?s=9cc8ce493f63f5c2c22db71b2061b4b8c21f43ba)](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticExtraSpecialProperties/)
[![Latest Stable Version](https://poser.pugx.org/mediawiki/semantic-extra-special-properties/version.png)](https://packagist.org/packages/mediawiki/semantic-extra-special-properties)
[![Packagist download count](https://poser.pugx.org/mediawiki/semantic-extra-special-properties/d/total.png)](https://packagist.org/packages/mediawiki/semantic-extra-special-properties)

Semantic Extra Special Properties (a.k.a. SESP) is an extension to [Semantic MediaWiki][smw] which adds some extra special properties to enabled content pages.


## Requirements

- PHP 7.0 to PHP 7.3
- MediaWiki 1.31 to 1.34
- Semantic MediaWiki 3.1 or later


## Installation

The recommended way to install Semantic Extra Special Properties is using [Composer](http://getcomposer.org) 
with [MediaWiki's built-in support for Composer](https://www.mediawiki.org/wiki/Composer).

Note that the required extension Semantic MediaWiki must be installed first according to the installation
instructions provided.

### Step 1

Change to the base directory of your MediaWiki installation. If you do not have a "composer.local.json" file yet,
create one and add the following content to it:

```
{
	"require": {
		"mediawiki/semantic-extra-special-properties": "~2.1"
	}
}
```

If you already have a "composer.local.json" file add the following line to the end of the "require"
section in your file:

    "mediawiki/semantic-extra-special-properties": "~2.1"

Remember to add a comma to the end of the preceding line in this section.

### Step 2

Run the following command in your shell:

    php composer.phar update --no-dev

Note if you have Git installed on your system add the `--prefer-source` flag to the above command.

### Step 3

Add the following line to the end of your "LocalSettings.php" file:

    wfLoadExtension( 'SemanticExtraSpecialProperties' );

### Step 4

Add the [configuration parameters](/docs/configuration.md) to the "LocalSettings.php" file according to your
requirements below the call to `wfLoadExtension` added in step 4.

### Step 5

This step may be skipped in case you are installing this extension for the first time to the respective wiki.
If this extension is being upgraded from an version earlier than 2.0.0 you have to change your configruation
in the "LocalSettings.php" file according to the consise [migration guide](/docs/migration-to-200.md).

### Step 6

Run the **["update.php"][mw-update]** maintenance script to ensure that property tables are properly
initialized.


## Usage

The annotation process for predefined properties is mostly done in the background and therefore does not need
any special interaction from a user but ["LocalSettings.php"][mw-localsettings] for SESP need to be enabled. 

For details about available configruation parameters, please have a look at the [configuration](docs/configuration.md)
document.

### Exif property names
You might want to check the labels for Exif properties. They are defined in MediaWiki core and being reused by SESP. It is possible that property names you already use will be prefedinied properties after activation of SESP. On the sepcial page "System messages" you can search for all messages with the prefix "Exif". One example: there is the message Exif-source that has the label "Source". If you use a property labeled "Source" already, you should change MediaWiki:Exif-source from "Source" to "Exif-source". 

## Contribution and support

If you have remarks, questions, or suggestions, please send them to semediawiki-users@lists.sourceforge.net. You can subscribe to this list [here](http://sourceforge.net/mailarchive/forum.php?forum_name=semediawiki-user).

If you want to contribute work to the project please subscribe to the developers mailing list and have a look at the [contribution guildline](/CONTRIBUTING.md). A list of people who have made contributions in the past can be found [here][contributors].

* [File an issue](https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties/issues)
* [Submit a pull request](https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties/pulls)
* Ask a question on [the mailing list](https://semantic-mediawiki.org/wiki/Mailing_list)

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
