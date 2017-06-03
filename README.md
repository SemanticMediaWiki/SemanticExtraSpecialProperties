# Semantic Extra Special Properties
[![Build Status](https://travis-ci.org/SemanticMediaWiki/SemanticExtraSpecialProperties.svg?branch=master)](https://travis-ci.org/SemanticMediaWiki/SemanticExtraSpecialProperties)
[![Code Coverage](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticExtraSpecialProperties/badges/coverage.png?s=c5563fd91abeb49b37a6ef999198530b6796dd3c)](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticExtraSpecialProperties/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticExtraSpecialProperties/badges/quality-score.png?s=9cc8ce493f63f5c2c22db71b2061b4b8c21f43ba)](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticExtraSpecialProperties/)
[![Latest Stable Version](https://poser.pugx.org/mediawiki/semantic-extra-special-properties/version.png)](https://packagist.org/packages/mediawiki/semantic-extra-special-properties)
[![Packagist download count](https://poser.pugx.org/mediawiki/semantic-extra-special-properties/d/total.png)](https://packagist.org/packages/mediawiki/semantic-extra-special-properties)
[![Dependency Status](https://www.versioneye.com/php/mediawiki:semantic-extra-special-properties/badge.png)](https://www.versioneye.com/php/mediawiki:semantic-extra-special-properties)

Semantic Extra Special Properties (a.k.a. SESP) is an extension to [Semantic MediaWiki][smw] which adds some extra special properties to enabled content pages.

## Requirements

- PHP 5.5 or later
- MediaWiki 1.27 or later
- Semantic MediaWiki 2.5 or later

## Installation

The recommended way to install Semantic Extra Special Properties is by using [Composer][composer]
with an entry in MediaWiki's `composer.json` or `composer.local.json`.

```json
{
	"require": {
		"mediawiki/semantic-extra-special-properties": "~2.0"
	}
}
```
1. From your MediaWiki installation directory, execute
   `composer require mediawiki/semantic-extra-special-properties:~2.0`
2. It is strongly recommended to rebuild existing semantic data and run
   Semantic MediaWiki's rebuild data process.
3. Navigate to _Special:Version_ on your wiki and verify that the package
   have been successfully installed.

## Usage

The annotation process for predefined properties is mostly done in the background
and therefore doesn't need any special interaction from a user but [`LocalSettings`][mw-localsettings] for
`SESP` need to be enabled.

For details about available settings, please have a look at the [configuration](docs/00-configuration.md) document.

## Contribution and support

If you have remarks, questions, or suggestions, please send them to semediawiki-users@lists.sourceforge.net. You can subscribe to this list [here](http://sourceforge.net/mailarchive/forum.php?forum_name=semediawiki-user).

If you want to contribute work to the project please subscribe to the
developers mailing list and have a look at the [contribution guildline](/CONTRIBUTING.md). A list of people who have made contributions in the past can be found [here][contributors].

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
[semver]: http://semver.org/
