This file contains the RELEASE-NOTES of the SemanticExtraSpecialProperties (a.k.a. SESP) extension.

### 1.5.0

Released on June 7, 2017.

* #74 Added `_PAGELGTH` for page length (size in bytes) collection on pages
* Localization updates from https://translatewiki.net

### 1.4.0

Released on January 22, 2017.

This release now requires MediaWiki 1.25+ and Semantic MediaWiki 2.3+ (#61).

* #47 Fixed support for the `_VIEWS` special property for MediaWiki 1.25+ which now requires the HitCounters extension (by Cindy Cicalese)
* #54 Fixed `Invalid or virtual namespace -1` exception (by James Hong Kong)
* #55 Fixed `NS_MEDIA` being detected instead of `NS_FILE` (by James Hong Kong)
* #57 Fixed issue with Composer when run locally (by Cindy Cicalese)
* #58 Adjusted lang.dep aliases
* #59 Fixed `0` annotation values (by James Hong Kong)
* #60 Fixed isse when stat failed for `filemtime():` (by James Hong Kong)
* #67 Fixed unserialize error in `ExifDataAnnotator` (by James Hong Kong)
* Several internal code changes (by James Hong Kong and Jeroen De Dauw)
* Localization updates from https://translatewiki.net

### 1.3.1

Released on July 18, 2015.

- #50 Fixed error with `_USEREDITCNT` on subpages in namespace "user"

### 1.3.0

Released on May 9, 2015.

- #43 Added `_USEREDITCNT` for user edit count collection on user pages

### 1.2.2

Released on December 31, 2014.

- #42 Fixed fatal during `importDump` for when a file doesn't exist

### 1.2.1

Released on July 21, 2014.

- Added compatibility with Semantic MediaWiki 2.x

### 1.2.0

Released on April 23, 2014.

- #25 Added MessageCache to improve registration and lookup performance
- #33 Added DefinitionReader to separate responsibilities

### 1.1.0

Released on April 9, 2014.

- #31 Fixed error when a User page is created with a subpage
- #32 Migrate to JSON i18n

### 1.0.0

Released on February 23, 2014.

Version 1.0 is a complete rewrite of the existing implementation to allow sufficient test integration which made it necessary
to split the original file into different classes (force encapsulation), eliminate GLOBALS (where necessary inject
configuration via the constructor), and enable service injection (increase inversion of control).

`Exif` and `ShorUrl` handling has been moved into separate classes, property registration has been uncoupled from the
functional implementation. Property definitions no longer reside within PHP and have been moved into `json` file for
easier access and configurability.

Due to those internal changes and the introduced test integration, 1.0 requires Semantic MediaWiki 1.9. It is strongly
recommended to run `update.php` together with a `SMW_refreshData.php`.

For details about the rewrite, its discussion, and changes see #10.

- Added support for installation via Composer
- Added Travis-CI integration
- Fixed PHP strict notices
- #10 Fixed incorrect `_REVID` assignment
- #10 Added `_PAGEID` for page ID collection
- #10 Fixed incorrect ``_NTREV`` assignment
- #10 Added ``'_EXIFDATA'`` collection (see [definitions](/src/Definition/definitions.json)) which are stored as subobject
- #10 Fixed "wfMsgGetKey" usage
- #10 Fixed initialization value "is not a number" issue
- #10 Added `sespUseAsFixedTables` setting
- #10 Added unit and integrations tests
- #13 Added I18n updates
- #20 Added possibility to alter property visibility via the definitions file
- #21 Extended Exif property definitions

### 0.2.7

Released on October 22, 2012.

- Requires MediaWiki 1.20
- Use WikiPage instead of Article

### 0.2.6

Released on October 5, 2012.

- Fixed bug sometimes causing a crash on pagesave on MW 1.20+
- Added `_USERREG` special property

### 0.2.5

Released on August 1, 2012.

- Bugfixes
- Error message fixes by Nischayn22

### 0.2.4

Released on July 28, 2012.

- Requires MediaWiki 1.19
- Added some image meta data (exif) properties
- Bug fix by Van de Bugger

### 0.2.3

Released on May 10, 2012.

- Added `_SHORTURL` special property
- Translation updates, German
- Fix for bug with first author for certain special pages, by Van de Bugger

### 0.2.2

Released on February 9, 2012.

- $smwgPageSpecialProperties replaced by `$sespSpecialProperties`
- Added `_MIMETYPE` (mime type, mediatype) special property

### 0.2.1

Released on January 8, 2012.

- German translation by Kghbln
- Better method to fetch list of `_EUSER` (getContributors and getUser, instead of getLastNAuthors. Anonymous users will never be listed)

### 0.2.0

Released on January 4, 2012.

- Only tested with SMW 1.7 and MW 1.18.
- Changed name for `_EUSER` and `_CUSER` props in both English and Swedish, article ###> page for clarity.
- Using $smwgPageSpecialProperties2 to chose which properties to set, the same way as $smwgPageSpecialProperties
is used for built in special properties
- Ignoring `_VIEWS` if statistics are disables in "LocalSettings.php"
- Added `_SUBP`, `_NREV` and `_NTREV` special properties

### 0.1 (2011-11-25)

Released on November 25, 2011.

* Initial release
