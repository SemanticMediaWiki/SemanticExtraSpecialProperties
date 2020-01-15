
# Configuration

Properties that are planned to be included need to be specified in the ["LocalSettings.php"][mw-localsettings] file using the `$sespgEnabledPropertyList` array. By default the array is empty, i.e. no special property is being annotated to a page.

```php
$sespgEnabledPropertyList = [
	'_EUSER',
	'_CUSER',
	...
];
```
## Property definitions

Property identifiers (see ["definitions.json"](/data/definitions.json) file) are used to specify which of the properties are enabled. An identifier is an internal `ID` which is not to be used during user interaction (e.g. handling in `#ask` queries) instead the property label should be used as reference.

### Labels

Property labels differ according to the language the wiki was set up. An easy way to identify those used labels is to navigate to the "Special:Properies" page that lists all available properties including properties provided by this extension.

### Identifiers

- `_EUSER` adds a property called "Page author" which records all users that edited a page (*expensive*; use with care)
- `_CUSER` adds a property called "Page creator" which records the user that created a page
- `_REVID` adds a property called "Revision ID" which records the current revision ID of a page
- `_PAGEID` adds a property called "Page ID" which records the page ID of a page
- `_PAGELGTH` adds a property called "Page length" which records the page length of a page
- `_NREV` adds a property called "Number of revisions" which records estimated number of total revisions of a page
- `_NTREV` adds a property called "Number of talk page revisions" which records an estimated number of total revisions of a talk page
- `_SUBP` adds a property called "Subpage" which records all subpages to a page
- `_USERREG` adds a property called "User registration date" to user pages which records the user's registration date
- `_USEREDITCNT` adds a property called "User edit count" to user pages which records the user's edit count
- `_USERBLOCK` adds a property called "User block" to user pages which records the user's block status
- `_USERRIGHT` adds a property called "User right" to user pages which records the user's assigned rights
- `_USERGROUP` adds a property called "User group" to user pages which records the user's assigned groups
- `_EXIFDATA` adds properties called "Exif data" to file pages which records image metadata (Exif data)

#### Identifiers with further dependencies

- `_SHORTURL` adds a property called "Short URL" which records short URLs of a page if the [ShortUrl][ShortUrl] extension is installed, and if there is a shortened URL for the current page
- `_VIEWS` adds a property called "Number of page views" which records the number of page views of a page if the [HitCounters][HitCounters] extension is installed. This is required starting with MediaWiki 1.25 and later. In earlier versions of MediaWiki this special property used to work out of the box if enabled. Note that depending on local settings this value might not be very up to date. If [`$wgDisableCounters`][$wgDisableCounters] is set to "true" this property will never be set.
- `_APPROVED` adds a property called "Approved revision" which records the approvement state of a page if the [Approved Revs][Approved Revs] extension is installed
- `_APPROVEDBY` adds a property called "Approved by" which records the user that approved a page if the [Approved Revs][Approved Revs] extension is installed
- `_APPROVEDDATE` adds a property called "Approved date" which records the date a page was approved if the [Approved Revs][Approved Revs] extension is installed
- `_APPROVEDSTATUS` adds a property called "Approval status" which records the approvement status of a page if the [Approved Revs][Approved Revs] extension is installed

## Additional configuration

#### Fixed tables

Setting `$sespgUseFixedTables` to "true" enables to setup properties as [fixed properties][fixedprop] in order to
improve data access. Doing so is recommended. Note that you have to run the ["update.php"][mw-update] maintenance script
from your wiki's base directory after setting this parameter for the required tables to be created.

Running the [data refresh][data-refresh] afterwards is recommended as well and should be done every time a special property
is added to the `$sespgEnabledPropertyList` array.

#### Bot edits

Setting `$sespgExcludeBotEdits` to "true" causes bot edits via user accounts in usergroup "bot" to be ignored when storing
data for the special properties activated. However this does not affect the page creator property (`_CUSER`).

#### Property definitions

Details about available properties can be found in the ["definitions.json"](/data/definitions.json) file. The file also contains information about the visibility (display in the Factbox etc.) of a property, to alter the characterisctics of non-subobject related properties one can set `show` to `true` for each definition.

#### Cache usage

Setting `$sespgLabelCacheVersion` to "false" will cease to use the special property label cache at all. Otherwise this is
used as an internal modifier to allow resetting the cache with an arbitrary version.

## Privacy

Please note that users that are otherwise hidden to some usergroup might be revealed by this extension, as the `_EUSER`
property will list all authors for everyone.

The Exchangeable image file format (and thereof its Exif tags) can contain metadata about a location which can pose
a [privacy issue][privacy].

&larr; [README](README.md) | [Extension](01-extension.md) | [Migration to 2.0.0](migration-to-200.md) &rarr;

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
[Approved Revs]: https://www.mediawiki.org/wiki/Extension:Approved_Revs
[data-refresh]: https://www.semantic-mediawiki.org/wiki/Help:Maintenance_script_rebuildData.php
[mw-update]: https://www.mediawiki.org/wiki/Manual:Update.php
[mw-localsettings]: https://www.mediawiki.org/wiki/Localsettings
[mw-contentlang]: https://www.mediawiki.org/wiki/Content_language
