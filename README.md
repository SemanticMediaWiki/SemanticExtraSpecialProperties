[![Build Status](https://travis-ci.org/SemanticMediaWiki/SemanticExtraSpecialProperties.png?branch=master)](https://travis-ci.org/SemanticMediaWiki/SemanticExtraSpecialProperties)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticExtraSpecialProperties/badges/quality-score.png?s=9cc8ce493f63f5c2c22db71b2061b4b8c21f43ba)](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticExtraSpecialProperties/)
[![Latest Stable Version](https://poser.pugx.org/mediawiki/semantic-extra-special-properties/version.png)](https://packagist.org/packages/mediawiki/semantic-extra-special-properties)
[![Packagist download count](https://poser.pugx.org/mediawiki/semantic-extra-special-properties/d/total.png)](https://packagist.org/packages/mediawiki/semantic-extra-special-properties)
[![Dependency Status](https://www.versioneye.com/php/mediawiki:semantic-extra-special-properties/badge.png)](https://www.versioneye.com/php/mediawiki:semantic-extra-special-properties)

A [Semantic MediaWiki][smw] extension which adds some extra special properties to all content pages in a wiki.

## Installation

The recommended way to install this extension is by using [Composer][composer]. Just add the following to the MediaWiki `composer.json` file and run the ``php composer.phar install/update`` command.

```json
{
	"require": {
		"mediawiki/semantic-extra-special-properties": "~0.3"
	}
}
```
## Requirements
- PHP 5.3 or later
- MediaWiki 1.20 or later
- Semantic MediaWiki 1.9 or later
- When using MySQL 5 or later or when using SQLite 3 or later

## Customizing

Properties to be included needs to be specified in the `LocalSettings.php` using the `$sespSpecialProperties` array.

- `_EUSER` add all users that edited this article (expensive)
- `_CUSER` add user that created this article
- `_REVID` add property with current revision ID
- `_PAGEID` add property the page Id
- `_VIEWS` add property with number of page views. Note that depending on local settings this value might not be very up to date. If `$wgDisableCounters` is set to true this property will never be set
- `_NREV` add property showing an estimated number of total revisions
- `_NTREV` same but for the talk page, i.e. showing how much discussion is going on around this page
- `_SUBP` add properties for subpages
- `_MIMETYPE` add mimetype in case the article object is NS_IMAGE/NS_FILE
- `_MEDIATYPE` add mediatype in case the article object is NS_IMAGE/NS_FILE
- `_SHORTURL` add short URL if Extension:ShortUrl is installed, and there is a shortened URL for the current page
- `_USERREG` add a property to user pages with the users registration date
- `_EXIFDATA` add properties based on image metadata (exif data), when available

Compound customizing for special properties can be maintained as:
```
$sespSpecialProperties = array(
	'_EUSER',
	'_CUSER',
	...
);
```

After upgrading from 0.1 to 0.2 or higher, you will need to run the SMW refresh script for things for work.

To ignore all users with a bot flag when creating the article author properties. (This does not affect the article creator property.)

```
$wgSESPExcludeBots = true;
```

`$sespUseAsFixedTables` enables to setup properties as [fixed properties][fixedprop] in order to improve data access.

### Property definitions
Details about property definitions can be found in [definitions.json](/src/definitions.json).

### Privacy
Please note that users that are otherwise hidden to some usergroup might be revealed by this extension, as the `_EUSER` property will list all authors for everyone.

The Exchangeable image file format (and thereof its Exif tags) can contain metadata about a location which can pose a [privacy issue][privacy].

## Tests
The extension provides unit tests that covers the core-functionality as well as testing its integration with [Semantic MediaWiki][smw]. The tests are normally run via a [continues integration platform][travis] that includes:
- PHP 5.3, MySQL, and MW master
- PHP 5.4, SQLite, and MW 1.22.0
- PHP 5.5, MySQL, and MW 1.20.0

Tests can be run manually using the PHPUnit configuration file found in the root directory. For details on how to execute tests together with MediaWiki, see the [manual][mw-testing].

## Changelog
For details about changed behaviour, added customizing or feature, see the [Changelog](CHANGELOG.md).

## Credits
- 0.3 is a complete re-write and has been implemented by mwjames.
- 0.2.8 enhanced the exif data handling provided by Stephan Gambke.
- Originally written for [säsongsmat.nu][säsongsmat] by Leo Wallentin (leo_wallentin (at) hotmail.com).

## License
GNU GPL v2+

## Disclaimer

The source code is provided as-is, without warranty and does not warrant or endorse and does not assume and will not have any liability or responsibility for any damage or loss.

[composer]: https://getcomposer.org/
[mwcomposer]: https://www.mediawiki.org/wiki/Composer
[smw]: https://www.semantic-mediawiki.org/wiki/Semantic_MediaWiki
[privacy]: https://en.wikipedia.org/wiki/Exchangeable_image_file_format#Privacy_and_security
[travis]: https://travis-ci.org/SemanticMediaWiki/SemanticExtraSpecialProperties
[säsongsmat]: http://säsongsmat.nu
[mw-testing]: https://www.mediawiki.org/wiki/Manual:PHP_unit_testing
[fixedprop]: https://www.semantic-mediawiki.org/wiki/Help:Fixed_properties