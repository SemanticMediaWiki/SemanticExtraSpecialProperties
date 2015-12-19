## Version 1.x

### 1.3.2 (2015-12-??)

* #54 Guard against `Invalid or virtual namespace -1` exception

### 1.3.1

Released on 2015-07-18

- #50 Fix error with `_USEREDITCNT` on subpages in namespace "user"

### 1.3.0

Released on 2015-05-09

- #43 Added `_USEREDITCNT` for user edit count collection on user pages

### 1.2.2

Released on 2014-12-31

- #42 Fix fatal during `importDump` for when a file doesn't exist

### 1.2.1

Released on 2014-07-21

- Added compatibility with Semantic MediaWiki 2.x

### 1.2.0

Released on 2014-04-23

- #25 Added MessageCache to improve registration and lookup performance
- #33 Added DefinitionReader to separate responsibilities

### 1.1.0

Released on 2014-04-09

- #31 Fixed error when a User page is created with a subpage
- #32 Migrate to JSON i18n

### 1.0.0

Released on 2014-02-23.

1.0 is a complete rewrite of the existing implementation to allow sufficient test integration which made it necessary
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

## Version 0.x

### 0.2.7

Released on 2012-10-22.

- Requires MediaWiki 1.20
- Use WikiPage instead of Article

### 0.2.6

Released on 2012-10-05.

- Fixed bug sometimes causing a crash on pagesave on MW 1.20+
- Added `_USERREG`

### 0.2.5

Released on 2012-08-01.

- Bugfixes
- Error message fixes by Nischayn22

### 0.2.4

Released on 2012-07-28.

- Requires MediaWiki 1.19
- Add some image meta data (exif) properties
- Bug fix by Van de Bugger

### 0.2.3

Released on 2012-05-10.

- add `_SHORTURL`
- Translation updates, German
- Fix for bug with first author for certain special pages, by Van de Bugger

### 0.2.2

Released on 2012-02-09.

- $smwgPageSpecialProperties replaced by `$sespSpecialProperties`
- Added `_MIMETYPE` (mime type, mediatype)

### 0.2.1

Released on 2012-01-08.

- German translation by Kghbln
- Better method to fetch list of `_EUSER` (getContributors and getUser, instead of getLastNAuthors. Anonymous users
will never be listed)

### 0.2.0

Released on 2012-01-04.

- Only tested with SMW 1.7 and MW 1.18.
- Changed name for `_EUSER` and `_CUSER` props in both English and Swedish, article ###> page for clarity.
- Using $smwgPageSpecialProperties2 to chose which properties to set, the same way as $smwgPageSpecialProperties
is used for built in special properties
- Ignoring `_VIEWS` if statistics are disables in LocalSettings
- Added `_SUBP`, `_NREV` and `_NTREV`

### 0.1

Released on 2011-11-25.

* Initial release
