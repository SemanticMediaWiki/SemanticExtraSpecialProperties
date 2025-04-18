# Semantic Extra Special Properties
[![CI](https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties/actions/workflows/ci.yaml/badge.svg)](https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties/actions/workflows/ci.yaml)
[![codecov](https://codecov.io/gh/SemanticMediaWiki/SemanticExtraSpecialProperties/branch/master/graph/badge.svg?token=NP47aFjC7X)](https://codecov.io/gh/SemanticMediaWiki/SemanticExtraSpecialProperties)
![Latest Stable Version](https://img.shields.io/packagist/v/mediawiki/semantic-extra-special-properties.svg)
![Total Download Count](https://img.shields.io/packagist/dt/mediawiki/semantic-extra-special-properties.svg)

Semantic Extra Special Properties is a [Semantic MediaWiki][smw] extension that 
adds some extra [special properties].


## Requirements

- PHP 8.1 or later
- MediaWiki 1.39 or later, tested up to MediaWiki 1.43
- Semantic MediaWiki 5.0 or later, tested up to SMW 5.0.1


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
		"mediawiki/semantic-extra-special-properties": "~4.0"
	}
}
```

If you already have a "composer.local.json" file, add the following line to the end of the "require"
section in your file:

    "mediawiki/semantic-extra-special-properties": "~4.0"

Remember to add a comma to the end of the preceding line in this section.

### Step 2

Run the following command in your shell:

    php composer.phar update --no-dev

If you have Git installed on your system, you can add the `--prefer-source` flag to the above command.

### Step 3

Add the following line to the end of your "LocalSettings.php" file:

    wfLoadExtension( 'SemanticExtraSpecialProperties' );

### Step 4

Add the [configuration parameters](/docs/configuration.md) to the "LocalSettings.php" file according to your
requirements below the call to `wfLoadExtension` added in step 4.

### Step 5

This step may be skipped if you install this extension for the first time in the respective wiki.
If this extension is being upgraded from a version earlier than 2.0.0 you have to change your configuration
in the "LocalSettings.php" file according to the concise [migration guide](/docs/migration-to-200.md).

### Step 6

Run the **["update.php"][mw-update]** maintenance script to ensure that property tables are properly
initialized.


## Usage

The annotation process for predefined properties is primarily done in the background and, therefore, does not need
any special interaction from a user, but ["LocalSettings.php"][mw-localsettings] for SESP needs to be enabled. 

For details about available configuration parameters, please have a look at the [configuration](docs/configuration.md)
document.

### Exif property names
You should check the labels for Exif properties. They are defined in MediaWiki core and are being reused by SESP.
Property names you already use may be predefined properties after activation of SESP. On the special page
"System messages", you can search for all messages with the prefix "Exif". One example: There is the message
Exif-source that has the label "Source". If you use a property labeled "Source" already, you should change the system
message MediaWiki:Exif-source from "Source" to "Exif-source". 

## Contribution and support

If you have remarks, questions, or suggestions, please send them to semediawiki-users@lists.sourceforge.net.
You can subscribe to this list [here](http://sourceforge.net/mailarchive/forum.php?forum_name=semediawiki-user).

If you want to contribute work to the project, please subscribe to the developer's mailing list and look at the
[contribution guildline](/CONTRIBUTING.md). You can find a list of people who have contributed in the past
in the [contributors overview][contributors].

* [File an issue](https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties/issues)
* [Submit a pull request](https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties/pulls)
* Ask a question on [the mailing list](https://semantic-mediawiki.org/wiki/Mailing_list)

### Tests

This extension provides unit and integration tests and is run by a [continuous integration platform][github-actions]
but can also be executed locally using the shortcut command `composer phpunit` from the extension base directory.

## License

[GNU General Public License 2.0 or later][license]

## Release notes

View the [release notes](RELEASE-NOTES.md)

[composer]: https://getcomposer.org/
[license]: https://www.gnu.org/copyleft/gpl.html
[mwcomposer]: https://www.mediawiki.org/wiki/Composer
[smw]: https://www.semantic-mediawiki.org/wiki/Semantic_MediaWiki
[github-actions]: https://docs.github.com/en/actions
[mw-testing]: https://www.mediawiki.org/wiki/Manual:PHP_unit_testing
[mw-update]: https://www.mediawiki.org/wiki/Manual:Update.php
[mw-localsettings]: https://www.mediawiki.org/wiki/Localsettings
[contributors]: https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties/graphs/contributors
[semver]: https://semver.org/
[special properties]: https://www.semantic-mediawiki.org/wiki/Help:Special_properties
