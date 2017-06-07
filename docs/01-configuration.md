# Configuration

Properties that are planned to be included need to be specified in the [`LocalSettings.php`][mw-localsettings] file using the `$GLOBALS['sespSpecialProperties']` array. By default the array is empty, i.e. no special property is being annotated to a page.
```php
$GLOBALS['sespSpecialProperties'] = array(
	'_EUSER',
	'_CUSER',
	...
);
```
## Properties

Property identifiers (see [`definitions.json`](/src/Definition/definitions.json) ) are used to specify which of the properties are enabled. An identifier is an internal `ID` which is not to be used during user interaction (e.g. handling in `#ask` queries) instead the property label should be used as reference.

### Labels

Property labels differ according to the language the wiki was set up. An easy way to identify those used labels is to navigate to the "Special:Properies" page that lists all available properties including properties provided by this extension.

Property labels are displayed in accordance with the maintained [content language][mw-contentlang]. Message keys and labels are retrieved from cache where in case message content is altered (`definitions.json` or message file), the cache will automatically be purged. Changing the default cache type can be achieved by modifying the [`$sespCacheType`][mw-cachetype] customizing.

### Identifier

- `_EUSER` adds a property with all users that edited this page (expensive; use with care)
- `_CUSER` adds a property with the user that created this page
- `_REVID` adds a property with current revision ID
- `_PAGEID` adds a property with the page ID
- `_PAGELGTH` adds a property with the page length (in bytes)
- `_NREV` adds a property showing an estimated number of total revisions of a page
- `_NTREV` same as `_NREV` but for the talk page, i.e. showing how much discussion is going on around this page
- `_SUBP` adds a property with all subpages
- `_USERREG` adds a property to user pages with the users registration date
- `_USEREDITCNT` add a property to user pages with the users edit count
- `_EXIFDATA` adds properties based on image metadata (Exif data), when available and in case it is a `NS_FILE` namespace object data are stored as a [subobject][subobject]. Details on available Exif data can be found [here](/src/Definition/definitions.json).


#### Properties with further dependencies

- `_SHORTURL` adds short URL if the [ShortUrl][ShortUrl] extension is installed, and there is a shortened URL for the current page
- `_VIEWS` adds a property with number of page views if the [HitCounters][HitCounters] extension is installed. This is required starting with MediaWiki 1.25 and later. In earlier versions of MediaWiki this special property used to work out of the box if enabled. Note that depending on local settings this value might not be very up to date. If [`$wgDisableCounters`][$wgDisableCounters] is set to "true" this property will never be set.

#### Depreciated properties

- `_MIMETYPE` add MIME type in case the article object is in the `NS_FILE` namespace. Please use Semantic MediaWiki's
(≥ 1.9.1) [MIME type][MIME type] instead.
- `_MEDIATYPE` add media type in case the article object is in the `NS_FILE` namespace. Please use Semantic MediaWiki's
(≥ 1.9.1) [Media type][Media type] instead.

These properties may be removed in any further release of this extension.

## Additional configuration

#### Fixed tables

Setting `$sespUseAsFixedTables` to "true" enables to setup properties as [fixed properties][fixedprop] in order to
improve data access. Doing so is recommended. Note that you have to run the [`update.php`][mw-update] from your wiki's base directory after setting this parameter for the required tables to be created.

Running the [data refresh][data-refresh] afterwards is recommended as well and should be done every time a special property is added to the `$sespSpecialProperties` array.

#### Bot edits

Setting ``$wgSESPExcludeBots`` to "true" causes bot edits via user accounts in usergroup "bot" to be ignored when storing data for the special properties activated. However this does not affect the page creator property (`_CUSER`).

#### Property definitions

Details about available properties can be found in the [definitions.json](/src/Definition/definitions.json). The file also contains information about the visibility (display in the Factbox etc.) of a property, to alter the characterisctics of non-subobject related properties one can set `show` to `true` for each definition.

## Privacy

Please note that users that are otherwise hidden to some usergroup might be revealed by this extension,
as the `_EUSER` property will list all authors for everyone.

The Exchangeable image file format (and thereof its Exif tags) can contain metadata about a location which
can pose a [privacy issue][privacy].

[smw]: https://www.semantic-mediawiki.org/wiki/Semantic_MediaWiki
[subobject]: https://semantic-mediawiki.org/wiki/Subobject
[$wgDisableCounters]: https://www.mediawiki.org/wiki/Manual:$wgDisableCounters
[privacy]: https://en.wikipedia.org/wiki/Exchangeable_image_file_format#Privacy_and_security
[mw-testing]: https://www.mediawiki.org/wiki/Manual:PHP_unit_testing
[fixedprop]: https://www.semantic-mediawiki.org/wiki/Help:Fixed_properties
[MIME type]: https://semantic-mediawiki.org/wiki/Help:Special_property_MIME_type
[Media type]: https://semantic-mediawiki.org/wiki/Help:Special_property_Media_type
[ShortUrl]: https://www.mediawiki.org/wiki/Extension:ShortUrl
[HitCounters]: https://www.mediawiki.org/wiki/Extension:HitCounters
[data-refresh]: https://semantic-mediawiki.org/wiki/Help:Data_refresh#Examples
[mw-update]: https://www.mediawiki.org/wiki/Manual:Update.php
[mw-localsettings]: https://www.mediawiki.org/wiki/Localsettings
[mw-contentlang]: https://www.mediawiki.org/wiki/Content_language
[mw-cachetype]: https://www.mediawiki.org/wiki/Manual:$wgMainCacheType
