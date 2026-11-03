<?php
/**
 * This is an automatically generated baseline for Phan issues.
 * When Phan is invoked with --load-baseline=path/to/baseline.php,
 * The pre-existing issues listed in this file won't be emitted.
 *
 * This file can be updated by invoking Phan with --save-baseline=path/to/baseline.php
 * (can be combined with --load-baseline)
 */
return [
	// # Issue statistics:
	// PhanTypeMismatchArgumentNullable : 15+ occurrences
	// PhanUndeclaredClassMethod : 6 occurrences
	// PhanNonClassMethodCall : 5 occurrences
	// PhanPluginDuplicateConditionalNullCoalescing : 4 occurrences
	// PhanTypeArraySuspiciousNullable : 4 occurrences
	// PhanTypeMismatchProperty : 3 occurrences
	// PhanTypeMismatchPropertyProbablyReal : 3 occurrences
	// PhanTypeMismatchPropertyDefault : 2 occurrences
	// PhanUndeclaredMethod : 2 occurrences
	// MediaWikiNoEmptyIfDefined : 1 occurrence
	// PhanParamTooFewInPHPDoc : 1 occurrence
	// PhanRedundantCondition : 1 occurrence
	// PhanTypeMismatchReturn : 1 occurrence
	// PhanUndeclaredTypeProperty : 1 occurrence
	// PhanUnusedVariableCaughtException : 1 occurrence

	'file_suppressions' => [
		'src/AppFactory.php' => [
			'PhanTypeMismatchPropertyProbablyReal' => ['\\SESP\\AppFactory::getConnection', '\\SESP\\AppFactory::setConnection'],
			'PhanTypeMismatchReturn' => ['\\SESP\\AppFactory::getConnection']
		],
		'src/LabelFetcher.php' => [
			'PhanTypeMismatchProperty' => ['\\SESP\\LabelFetcher::__construct'],
			'PhanTypeMismatchPropertyDefault' => ['\\SESP\\LabelFetcher'],
			'PhanUndeclaredClassMethod' => ['\\SESP\\LabelFetcher::getLabelsFrom'],
			'PhanUndeclaredTypeProperty' => ['\\SESP\\LabelFetcher']
		],
		'src/PropertyAnnotators/ApprovedByPropertyAnnotator.php' => [
			'PhanTypeMismatchArgumentNullable' => ['\\SESP\\PropertyAnnotators\\ApprovedByPropertyAnnotator::addAnnotation']
		],
		'src/PropertyAnnotators/ApprovedDatePropertyAnnotator.php' => [
			'PhanNonClassMethodCall' => ['\\SESP\\PropertyAnnotators\\ApprovedDatePropertyAnnotator::getDataItem'],
			'PhanParamTooFewInPHPDoc' => ['\\SESP\\PropertyAnnotators\\ApprovedDatePropertyAnnotator::addAnnotation'],
			'PhanTypeMismatchPropertyProbablyReal' => ['\\SESP\\PropertyAnnotators\\ApprovedDatePropertyAnnotator::addAnnotation']
		],
		'src/PropertyAnnotators/ApprovedStatusPropertyAnnotator.php' => [
			'PhanTypeMismatchArgumentNullable' => ['\\SESP\\PropertyAnnotators\\ApprovedStatusPropertyAnnotator::addAnnotation']
		],
		'src/PropertyAnnotators/CreatorPropertyAnnotator.php' => [
			'PhanTypeMismatchArgumentNullable' => ['\\SESP\\PropertyAnnotators\\CreatorPropertyAnnotator::addAnnotation']
		],
		'src/PropertyAnnotators/DispatchingPropertyAnnotator.php' => [
			'PhanTypeMismatchProperty' => ['\\SESP\\PropertyAnnotators\\DispatchingPropertyAnnotator::initDefaultPropertyAnnotators']
		],
		'src/PropertyAnnotators/ExifPropertyAnnotator.php' => [
			'PhanTypeMismatchArgumentNullable' => ['\\SESP\\PropertyAnnotators\\ExifPropertyAnnotator::addAnnotation'],
			'PhanUndeclaredMethod' => ['\\SESP\\PropertyAnnotators\\ExifPropertyAnnotator::addAnnotation'],
			'PhanUnusedVariableCaughtException' => ['\\SESP\\PropertyAnnotators\\ExifPropertyAnnotator::makeDataItemTime']
		],
		'src/PropertyAnnotators/LinksToPropertyAnnotator.php' => [
			'MediaWikiNoEmptyIfDefined' => ['\\SESP\\PropertyAnnotators\\LinksToPropertyAnnotator::addAnnotation']
		],
		'src/PropertyAnnotators/PageContributorsPropertyAnnotator.php' => [
			'PhanTypeMismatchArgumentNullable' => ['\\SESP\\PropertyAnnotators\\PageContributorsPropertyAnnotator::addAnnotation']
		],
		'src/PropertyAnnotators/PageDescriptionPropertyAnnotator.php' => [
			'PhanTypeMismatchArgumentNullable' => ['\\SESP\\PropertyAnnotators\\PageDescriptionPropertyAnnotator::addAnnotation']
		],
		'src/PropertyAnnotators/PageIDPropertyAnnotator.php' => [
			'PhanRedundantCondition' => ['\\SESP\\PropertyAnnotators\\PageIDPropertyAnnotator::addAnnotation'],
			'PhanTypeMismatchArgumentNullable' => ['\\SESP\\PropertyAnnotators\\PageIDPropertyAnnotator::addAnnotation']
		],
		'src/PropertyAnnotators/PageImagesPropertyAnnotator.php' => [
			'PhanTypeMismatchArgumentNullable' => ['\\SESP\\PropertyAnnotators\\PageImagesPropertyAnnotator::addAnnotation']
		],
		'src/PropertyAnnotators/PageViewsPropertyAnnotator.php' => [
			'PhanTypeMismatchArgumentNullable' => ['\\SESP\\PropertyAnnotators\\PageViewsPropertyAnnotator::addAnnotation'],
			'PhanUndeclaredClassMethod' => ['\\SESP\\PropertyAnnotators\\PageViewsPropertyAnnotator::getPageViewCount'],
			'PhanUndeclaredMethod' => ['\\SESP\\PropertyAnnotators\\PageViewsPropertyAnnotator::getPageViewCount']
		],
		'src/PropertyAnnotators/RevisionIDPropertyAnnotator.php' => [
			'PhanTypeMismatchArgumentNullable' => ['\\SESP\\PropertyAnnotators\\RevisionIDPropertyAnnotator::addAnnotation']
		],
		'src/PropertyAnnotators/ShortUrlPropertyAnnotator.php' => [
			'PhanTypeMismatchArgumentNullable' => ['\\SESP\\PropertyAnnotators\\ShortUrlPropertyAnnotator::addAnnotation'],
			'PhanUndeclaredClassMethod' => ['\\SESP\\PropertyAnnotators\\ShortUrlPropertyAnnotator::getShortUrl', '\\SESP\\PropertyAnnotators\\ShortUrlPropertyAnnotator::getUrlPrefix']
		],
		'src/PropertyAnnotators/UserBlockPropertyAnnotator.php' => [
			'PhanTypeMismatchArgumentNullable' => ['\\SESP\\PropertyAnnotators\\UserBlockPropertyAnnotator::addAnnotation']
		],
		'src/PropertyAnnotators/UserEditCountPerNsPropertyAnnotator.php' => [
			'PhanTypeMismatchArgumentNullable' => ['\\SESP\\PropertyAnnotators\\UserEditCountPerNsPropertyAnnotator::addAnnotation']
		],
		'src/PropertyAnnotators/UserEditCountPropertyAnnotator.php' => [
			'PhanTypeMismatchArgumentNullable' => ['\\SESP\\PropertyAnnotators\\UserEditCountPropertyAnnotator::addAnnotation']
		],
		'src/PropertyAnnotators/UserGroupPropertyAnnotator.php' => [
			'PhanTypeMismatchArgumentNullable' => ['\\SESP\\PropertyAnnotators\\UserGroupPropertyAnnotator::addAnnotation']
		],
		'src/PropertyAnnotators/UserRegistrationDatePropertyAnnotator.php' => [
			'PhanTypeMismatchArgumentNullable' => ['\\SESP\\PropertyAnnotators\\UserRegistrationDatePropertyAnnotator::addAnnotation']
		],
		'src/PropertyAnnotators/UserRightPropertyAnnotator.php' => [
			'PhanTypeMismatchArgumentNullable' => ['\\SESP\\PropertyAnnotators\\UserRightPropertyAnnotator::addAnnotation']
		],
		'src/PropertyDefinitions.php' => [
			'PhanTypeArraySuspiciousNullable' => ['\\SESP\\PropertyDefinitions::deepGet', '\\SESP\\PropertyDefinitions::get', '\\SESP\\PropertyDefinitions::safeGet'],
			'PhanTypeMismatchPropertyDefault' => ['\\SESP\\PropertyDefinitions']
		],
		'src/PropertyRegistry.php' => [
			'PhanPluginDuplicateConditionalNullCoalescing' => ['\\SESP\\PropertyRegistry::addPropertyDefinition']
		],
	],
	// 'directory_suppressions' => ['src/directory_name' => ['PhanIssueName1', 'PhanIssueName2']] can be manually added if needed.
	// (directory_suppressions will currently be ignored by subsequent calls to --save-baseline, but may be preserved in future Phan releases)
];
