
# Extension

* [Repository extension](#repository-extension)
* [Local extension](#local-extension)

## Repository extension

The repository extension is meant to change the SESP repository itself in providing
additional services that can be enabled via the `$sespgEnabledPropertyList` setting.

```
$sespgEnabledPropertyList = [
	'_CUSER',
	'_VIEWS',
	'_PAGEID',
	'_ETX1'
];
```

Each extension is expected to be represented by a definition and a
corresponding `PropertyAnnotator` implementation.

### Property definition expansion

Expand the property definition in `definitions.json` with something like:

* `_ETX1` defines an external reference
* `id` (`___EXT1`) defines the internal reference for values that are stored and fetched from
* `type` (`num`) declares the expected [data type](https://www.semantic-mediawiki.org/wiki/Datatype) of a value
* `alias` (`sesp-property-ext-1`) declares a message key for the label alias
* `label` (`Ext ID`) free caption form
* `desc` (`sesp-property-ext-1-desc`) declares a message key for the decription of a property

**Examples**

```
"_ETX1": {
	"id": "___EXT1",
	"type": "_num",
	"alias": "sesp-property-ext-1",
	"label": "Ext ID",
	"desc": "sesp-property-ext-1-desc"
}
```

**PropertyAnnotator implementation**

- Create an individual class (e.g. `MyExt1PropertyAnnotator`) that implements the `PropertyAnnotator` interface,
  placed in the corresponding folder, and contain the details required for the value annotation supported by
  the related property
- Add a complementary test class (e.g. `MyExt1PropertyAnnotatorTest`) to test the newly added functionality
- Register the service with the `DispatchingPropertyAnnotator`
- Extend the `DispatchingPropertyAnnotator` test to cover the newly added service

## Local extension

The local extension mechanism has been introduced to avoid having to alter the `SESP` repository
directly and instead provide a method for simple local adaptation.

```
$sespgEnabledPropertyList = [
	'_CUSER',
	'_VIEWS',
	'_PAGEID',
	'_MY_CUSTOM1',
	'_MY_CUSTOM2'
];
```

`$sespgLocalDefinitions` contains encapsulate property definitions that
are only valid locally to a wiki and are loaded from the `LocalSettinsg.php`.

* Same fields are required as outlined in the "Repository extension" section
* Define a `callback` which expects a callable instance (either as static or Closure)

**Examples**

```
$sespgLocalDefinitions['_MY_CUSTOM1'] = [
	'id'    => '___MY_CUSTOM1',
	'type'  => '_wpg',
	'alias' => 'some-...',
	'label' => 'SomeCustomProperty',
	'callback'  => function( $appFactory, $property, $semanticData ) {
		return \SMW\DIWikiPage::newFromText( 'Foo' );
	}
];
```

```
$sespgLocalDefinitions['_MY_CUSTOM2'] = [
	'id'    => '___MY_CUSTOM2',
	'type'  => '_wpg',
	'alias' => 'some-...',
	'label' => 'SomeCustomProperty2',
	'callback'  => [ 'FooCustom', 'addAnnotation' ]
];

class FooCustom {

	/**
	 * @param AppFactory $appFactory
	 * @param DIProperty $property
	 * @param SemanticData $semanticData
	 */
	public static function addAnnotation( $appFactory, $property, $semanticData ) {
		return \SMW\DIWikiPage::newFromText( 'Bar' );
	}
}
```

&larr; [README](README.md) | [Configuration](configuration.md) | [Migration to 2.0.0](migration-to-200.md) &rarr;
