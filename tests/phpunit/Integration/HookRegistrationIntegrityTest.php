<?php

namespace SESP\Tests\Integration;

/**
 * Verifies that every hook declared in extension.json resolves to an existing
 * handler method. MediaWiki derives the handler method from the hook name and
 * never errors when it is absent — the hook simply never fires — so a renamed
 * handler method would otherwise regress silently.
 *
 * @group semantic-extra-special-properties
 * @coversNothing
 *
 * @license GPL-2.0-or-later
 * @since 7.0.0
 */
class HookRegistrationIntegrityTest extends \PHPUnit\Framework\TestCase {

	public function testEveryDeclaredHookResolvesToAHandlerMethod() {
		$extension = json_decode(
			file_get_contents( __DIR__ . '/../../../extension.json' ),
			true
		);

		$handlers = $extension['HookHandlers'] ?? [];
		$hooks = $extension['Hooks'] ?? [];

		$this->assertNotSame( [], $hooks, 'No hooks declared in extension.json' );

		foreach ( $hooks as $hook => $handlerName ) {
			$this->assertArrayHasKey(
				$handlerName,
				$handlers,
				"Hook '$hook' references undefined handler '$handlerName'"
			);

			$class = $handlers[$handlerName]['class'];

			// MediaWiki's HookContainer maps the hook name to the method name by
			// replacing ":", "\" and "-" with "_" and prefixing "on".
			$method = 'on' . strtr( $hook, ':\\-', '___' );

			$this->assertTrue(
				method_exists( $class, $method ),
				"Handler '$class' is missing method '$method' for hook '$hook'"
			);
		}
	}
}
