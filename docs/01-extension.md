
&larr; [Configuration](00-configuration.md)

## Repository extension

The repository extension includes changes to the SESP repository itself to provide
additional services which can be enabled via the `$sespSpecialProperties` setting.

```
$sespSpecialProperties = [
	'_CUSER',
	'_VIEWS',
	'_PAGEID',
	'_ETX1'
];
```

Each extension is expected to be represented by a definition expansion and an
implementation of a corresponding `PropertyAnnotator`.

### Property definition expansion

Expand the property definition in `definitions.json` with something like:

* `_ETX1` defines an external reference
* `id` (`___EXT1`) defines the internal reference values that are stored or fetched from
* `type` (`num`) declares the expected type of a value to be stored
* `alias` (`sesp-property-ext-1`) declares a message key for the label alias
* `label` (`Ext ID`) free form caption
* `desc` (`sesp-property-ext-1-desc`) declares a message key for the decription of the property

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

- Create an individual class (e.g. `MyExt1PropertyAnnotator`) that implements the `PropertyAnnotator` interface
  and is placed in the corresponding folder
- Add a complementary test class (e.g. `MyExt1PropertyAnnotatorTest`) that tests the newly added functionality
- Register the newly create service in `ExtraPropertyAnnotator`
- Extend the `ExtraPropertyAnnotator` test to cover the newly added service

## Local extension

Local extension is meant as functionality that does not alter the `SESP` repository
directly but instead provides a simple extension mechanism for local adaptation.

```
$sespSpecialProperties = [
	'_CUSER',
	'_VIEWS',
	'_PAGEID',
	'_MY_CUSTOM1',
	'_MY_CUSTOM2'
];
```

`$sespLocalPropertyDefinitions` is used to encapsulate property definitions that
are only valid locally to a wiki and are loaded on-the-fly from the
`LocalSettinsg.php`.

* Fields required are the same as outlined above in "Repository extension"
* `callback` expects a callable instance (either as static or Closure)

**Examples**

```
$sespLocalPropertyDefinitions['_MY_CUSTOM1'] = [
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
$sespLocalPropertyDefinitions['_MY_CUSTOM2'] = [
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
