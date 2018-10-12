# Configuration settings migration

Starting with version 2.0 of the Semantic Extra Special Properties extension the names of
configration parameters were harmonized. Thus the configuration parameter names must be
migrated to their new names. This file lists the obsolete configuration parameter names
and their new replacements:

1. `$sespUseAsFixedTables` was changed to `$sespgUseFixedTables`
2. `$sespPropertyDefinitionFile` was changed to `$sespgDefinitionsFile`
3. `$sespLocalPropertyDefinitions` was changed to `$sespgLocalDefinitions`
4. `$sespSpecialProperties` was changed to `$sespgEnabledPropertiesList`
5. `$sespLabelCacheVersion` was changed to `$sespgLabelCacheVersion`
6. `$wgSESPExcludeBots` was changed to `$sespgExcludeBotEdits`

&larr; [README](README.md) | [Configuration](configuration.md) | [Extension](extension.md) &rarr;
