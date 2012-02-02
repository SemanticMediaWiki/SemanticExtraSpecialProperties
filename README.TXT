This extensions, when used with Semantic MediaWiki, adds some extra special properties to all content pages in the wiki.
You will have to state explicitly in LocalSettings.php which properties you want to include, using the array $smwgPageSpecialProperties2. (Just like with $smwgPageSpecialProperties in recent versions of SMW.)

Please note that users that are otherwise hidden to some usergroup might be revealed by this extension,
as the EUSER property will list all authors for everyone.

Originally written for http://säsongsmat.nu, this extension works fine for me. It may or may not work for you.
I have only tested the latest version with MW 1.18 and SMW 1.7.

 Leo Wallentin
 leo_wallentin@hotmail.com
 @leo_wallentin
 http://xn--ssongsmat-v2a.nu

Installation:
=============
Place the extension files in $IP/extensions/SemanticExtraProperties/

Add the following line to LocalSettings.php:
require_once("$IP/extensions/SemanticExtraSpecialProperties/SemanticExtraSpecialProperties.php");

Use $smwgPageSpecialProperties2 in LocalSettings to enable properties offered here, like this:

$smwgPageSpecialProperties2[] = '_EUSER';// Add properties for editiong users
$smwgPageSpecialProperties2[] = '_CUSER';// Add property for creating user
$smwgPageSpecialProperties2[] = '_REVID';// Add property with current revision ID
$smwgPageSpecialProperties2[] = '_VIEWS';// Add property with number of page views. Note that depending on local settings this value might not be very up to date. If $wgDisableCounters is set to true this property will never be set
$smwgPageSpecialProperties2[] = '_NREV'; // Add property showing an estimated number of total revisions
$smwgPageSpecialProperties2[] = '_TNREV';// Same but for the talk page, i.e. showing how much discussion is going on around this page
$smwgPageSpecialProperties2[] = '_SUBP'; // Add properties for subpages

...or to enable them all in one line:
$smwgPageSpecialProperties2 = array( '_EUSER', '_CUSER', '_REVID', '_VIEWS', '_NREV', '_TNREV', '_SUBP' );

After upgrading from 0.1 to 0.2 or higher, you will need to run the SMW refresh script for things for work.

Add the following line to LocalSettings.php to ignore all users with bot flag when creating the article author properties.
(This does not affect the article creator property.)

$wgSESPExcludeBots = true;

Changelog:
==========
*0.2.1
**German translation by Kghbln
**Better method to fetch list of EUSER (getContributors and getUser, instead of getLastNAuthors. Anonymous users will never be listed)


*0.2.0
**Only tested with SMW 1.7 and MW 1.18.
**Changed name for EUSER and and CUSER props in both English and Swedish, article -> page for clarity.
**Using $smwgPageSpecialProperties2 to chose which properties to set, the same way as $smwgPageSpecialProperties is used for built in special properties
**Ignoring _VIEWS if statistics are disables in LocalSettings
**Added SUBP, NREV and TNREV

*0.1 First version

