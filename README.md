# Semantic Extra Special Properties
[![Build Status](https://travis-ci.org/SemanticMediaWiki/SemanticExtraSpecialProperties.png?branch=master)](https://travis-ci.org/SemanticMediaWiki/SemanticExtraSpecialProperties)
[![Code Coverage](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticExtraSpecialProperties/badges/coverage.png?s=c5563fd91abeb49b37a6ef999198530b6796dd3c)](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticExtraSpecialProperties/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticExtraSpecialProperties/badges/quality-score.png?s=9cc8ce493f63f5c2c22db71b2061b4b8c21f43ba)](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticExtraSpecialProperties/)
[![Latest Stable Version](https://poser.pugx.org/mediawiki/semantic-extra-special-properties/version.png)](https://packagist.org/packages/mediawiki/semantic-extra-special-properties)
[![Packagist download count](https://poser.pugx.org/mediawiki/semantic-extra-special-properties/d/total.png)](https://packagist.org/packages/mediawiki/semantic-extra-special-properties)
[![Dependency Status](https://www.versioneye.com/php/mediawiki:semantic-extra-special-properties/badge.png)](https://www.versioneye.com/php/mediawiki:semantic-extra-special-properties)

Semantic Extra Special Properties (a.k.a. SESP) is an extension to [Semantic MediaWiki][smw] which adds some extra special properties to enabled content pages.

## Requirements

- PHP 5.3 or later
- MediaWiki 1.20 or later
- Semantic MediaWiki 1.9 or later
- When using MySQL 5 or later or when using SQLite 3 or later

## Installation

The recommended way to install this extension is by using [Composer][composer]. Just add the following to the
MediaWiki `composer.json` file and run the ``php composer.phar install/update`` command.

```json
{
	"require": {
		"mediawiki/semantic-extra-special-properties": "~1.2"
	}
}
```
After upgrading this extension to a newer version it is strongly recommended to rebuild existing semantic data by running the [data refresh][data-refresh] command from the base directory.

## Configuration

The [`LocalSettings.php`][mw-localsettings] needs additional settings in order to enable `SESP` property annotations. For details on how to adjust the settings, please have a look at the [configuration guideline](CONFIGURATION.md).

```php
$GLOBALS['sespSpecialProperties'] = array(
	'_EUSER',
	'_CUSER',
	...
);
```

## Changelog

SESP follows the `<major>`.`<minor>`.`<patch>` [semantic versioning][semver] increment schema. For details about changes, added customizing or features, see the [Changelog](CHANGELOG.md).

## Contribution and support

This extension was originally written for [säsongsmat.nu][säsongsmat] by Leo Wallentin (leo_wallentin (at) hotmail.com).

If you have remarks, questions, or suggestions, please send them to semediawiki-users@lists.sourceforge.net. You can subscribe to this list [here](http://sourceforge.net/mailarchive/forum.php?forum_name=semediawiki-user).

If you want to contribute work to the project please subscribe to the
developers mailing list and have a look at the [contribution guildline](/CONTRIBUTING.md). A list of people who have made contributions in the past can be found [here][contributors].

* [File an issue](https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties/issues)
* [Submit a pull request](https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties/pulls)
* Ask a question on [the mailing list](https://semantic-mediawiki.org/wiki/Mailing_list)
* Ask a question on the #semantic-mediawiki IRC channel on Freenode.

### Tests

The library provides unit tests that covers the core-functionality normally run by the [continues integration platform][travis]. Tests can also be executed [manual][mw-testing] using the PHPUnit configuration file found in the root directory.

## License

[GNU General Public License 2.0 or later][licence]

[composer]: https://getcomposer.org/
[licence]: https://www.gnu.org/copyleft/gpl.html
[mwcomposer]: https://www.mediawiki.org/wiki/Composer
[smw]: https://www.semantic-mediawiki.org/wiki/Semantic_MediaWiki
[travis]: https://travis-ci.org/SemanticMediaWiki/SemanticExtraSpecialProperties
[mw-testing]: https://www.mediawiki.org/wiki/Manual:PHP_unit_testing
[data-refresh]: https://semantic-mediawiki.org/wiki/Help:Data_refresh#Examples
[mw-update]: https://www.mediawiki.org/wiki/Manual:Update.php
[mw-localsettings]: https://www.mediawiki.org/wiki/Localsettings
[contributors]: https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties/graphs/contributors
[säsongsmat]: http://säsongsmat.nu
[semver]: http://semver.org/
