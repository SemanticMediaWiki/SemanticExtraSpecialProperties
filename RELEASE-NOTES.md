This file contains the RELEASE-NOTES of the **Semantic Extra Special Properties** (a.k.a. SESP) extension.

### 4.0.0

Released on 2025-03-13.

* New minumum required versions:
  * MediaWiki 1.39
  * PHP 8.1
* Support for newer MediaWiki versions
* Support for newer Semantic MediaWiki versions
* Improved testing and CI
* fix (Links to): incorrect definition for _LINKSTO (#247)
* feat: add LinksTOPropertyAnnotator (#216)
* chg: update the underlying schema for SMW 3.2. (#150)
* chg: Remove creation of dynamic property (#225)

### 3.0.5

Released on 2023-12-26.

* Fixed PHP 8.1 deprecation warning in `PropertyDefinitions::getIterator`

### 3.0.4

Released on 2023-04-08.

* replace deprecated `SMWStore::updateDataBefore` hook with `SMW::Store::BeforeDataUpdateComplete` (gesinn-it-gea)
* added handler for ApprovedRevsRevisionApproved and ApprovedRevsRevisionUnapproved to update the cache (gesinn-it-gea)
* SMW's ApplicationFactory has been renamed to ServicesFactory (gesinn-it-gea)
* Remove "Approved date" property when unapproving (gesinn-it-wam)
* Improved testing / CI using docker-compose & make (gesinn-it-wam)

### 3.0.3

Released on 2023-03-20.

* #209 Allow approval status to be set to 'approved' (gesinn-it-wam)
* #208 Allow `_APPROVEDDATE` property to be enabled (gesinn-it-wam)
* Localization updates from https://translatewiki.net

### 3.0.2

Released on 2023-03-08.

* Improved MediaWiki 1.39 compatibility
  * Replace usage of `User::getRights` (thanks Hamish Slater)
* Localization updates from https://translatewiki.net

### 3.0.1

Released on 2022-01-26.

* Fixed Semantic MediaWiki 4.x compatibility issue (thanks Jeroen De Dauw)

### 3.0.0

Released on 2022-01-25.

* Minimum PHP version changed from 7.0 to 7.3
* Minimum MediaWiki version changed from 1.31 to 1.35
* Added compatibility with Semantic MediaWiki 4.x
* Removed compatibility with Semantic MediaWiki 3.x
* Localization updates from https://translatewiki.net

### 2.1.0

Released on 2020-02-09.

* Minimum requirement for
  * PHP changed to version 7.0
  * MediaWiki changed to version 1.31
  * Semantic MediaWiki changed to version 3.1
* #114 Added guard against invalid time strings within annotated data (by James Hong Kong)
* #116 Added support for the property group schemas ("sesp.group.json") (by James Hong Kong)
* #132 Replaced deprecated `DB_SLAVE` constant by `DB_REPLICA` (by Ammar Abdulhamid)
* #138 Added missing system messages providing special property descriptions (by Karsten Hoffmeyer)
* Several internal code changes and bug fixes (by James Hong Kong)
* Improved documentation of the extension's functionality (by Bernhard Krabina and Karsten Hoffmeyer)
* Localization updates from https://translatewiki.net

### 2.0.0

Released on 2018-10-12.

This release now requires MediaWiki 1.27+ and Semantic MediaWiki 3.0+ (#100).

Note that the configuration parameter names were renamed (#105). See the [migration guide](/docs/migration-to-200.md)
for a concise overview of the changes. Moreover special page "SemanticMediaWiki (`Special:SemanticMediaWiki`) will
inform about required configuration changes.

* Complete refactoring of the extension to allow for better extensibility and testability (by James Hong Kong)
* #16 Extended the `Exif` attributes (by James Hong Kong)
* #48 Fixed the registration of property tables (by James Hong Kong)
* #81 Made special property labels use user language (by James Hong Kong)
* #82 Added check null on edit count, refs #77 (by James Hong Kong)
* #83 Added more integration tests (by James Hong Kong)
* #84 Added DispatchingPropertyAnnotator (by James Hong Kong)
* #85 Added LabelFetcher with cache layer for improved performance (by James Hong Kong)
* #86 Converted to PHP 5.4+ short array syntax (by Karsten Hoffmeyer)
* #89 Changed ambiguous exif labels (by James Hong Kong)
* #91 Removed backwards compatible I18n shim (by Karsten Hoffmeyer)
* #95 Added `_APPROVED` for approvement state collection on pages, requires the Approved Revs extension (by Mark. A. Hershberger)
* #98 Added `_APPROVEDBY` for approving user collection on pages, `_APPROVEDDATE` for approved date collection on pages and `_APPROVEDSTATUS` for approvement status collection on pages, requires the Approved Revs extension (by Mark. A. Hershberger)
* #100 Added `_USERBLOCK` for user block status collection on user pages, `_USERRIGHT` for user rights collection on user pages and `_USERGROUP` for user groups collection on user pages (by James Hong Kong)
* #105 Renamed und harmonized configruation prarameter names (by James Hong Kong)
* Localization updates from https://translatewiki.net

### 1.5.0

Released on 2017-06-07.

* #74 Added `_PAGELGTH` for page length (size in bytes) collection on pages (by James Hong Kong)
* Localization updates from https://translatewiki.net

### 1.4.0

Released on 2017-01-22.

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

Released on 2015-07-18.

- #50 Fixed error with `_USEREDITCNT` on subpages in namespace "user"

### 1.3.0

Released on 2015-05-09.

- #43 Added `_USEREDITCNT` for user edit count collection on user pages

### 1.2.2

Released on 2014-12-31.

- #42 Fixed fatal during `importDump` for when a file doesn't exist

### 1.2.1

Released on 2014-07-21.

- Added compatibility with Semantic MediaWiki 2.x

### 1.2.0

Released on 2014-04-23.

- #25 Added MessageCache to improve registration and lookup performance
- #33 Added DefinitionReader to separate responsibilities

### 1.1.0

Released on 2014-04-09.

- #31 Fixed error when a User page is created with a subpage
- #32 Migrate to JSON i18n

### 1.0.0

Released on 2014-02-23.

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

Released on 2012-10-22.

- Requires MediaWiki 1.20
- Use WikiPage instead of Article

### 0.2.6

Released on 2012-10-05.

- Fixed bug sometimes causing a crash on pagesave on MW 1.20+
- Added `_USERREG` special property

### 0.2.5

Released on 2012-08-01.

- Bugfixes
- Error message fixes by Nischayn22

### 0.2.4

Released on 2012-07-28.

- Requires MediaWiki 1.19
- Added some image meta data (exif) properties
- Bug fix by Van de Bugger

### 0.2.3

Released on 2012-05-10.

- Added `_SHORTURL` special property
- Translation updates, German
- Fix for bug with first author for certain special pages, by Van de Bugger

### 0.2.2

Released on 2012-02-09.

- $smwgPageSpecialProperties replaced by `$sespSpecialProperties`
- Added `_MIMETYPE` (mime type, mediatype) special property

### 0.2.1

Released on 2012-01-08.

- German translation by Kghbln
- Better method to fetch list of `_EUSER` (getContributors and getUser, instead of getLastNAuthors. Anonymous users will never be listed)

### 0.2.0

Released on 2012-01-04.

- Only tested with SMW 1.7 and MW 1.18.
- Changed name for `_EUSER` and `_CUSER` props in both English and Swedish, article ###> page for clarity.
- Using $smwgPageSpecialProperties2 to chose which properties to set, the same way as $smwgPageSpecialProperties
is used for built in special properties
- Ignoring `_VIEWS` if statistics are disables in "LocalSettings.php"
- Added `_SUBP`, `_NREV` and `_NTREV` special properties

### 0.1 (2011-11-25)

Released on November 25, 2011.

* Initial release
