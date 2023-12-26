<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 *
 * @internal
 */
final class OAuth2ScopePatternTest extends TestCase
{
    public function test_matches(): void
    {
        $pattern = new OAuth2ScopePattern('{foo}:bar', ':');

        self::assertNotNull($pattern->match('hi:bar'));
    }

    public function test_substitutes(): void
    {
        $pattern = new OAuth2ScopePattern('{foo}:bar', ':');

        /**
         * @var Map<string,string> $variables
         */
        $variables = new Map();
        $variables->put('foo', 'baz');

        self::assertSame('baz:bar', $pattern->withVariables($variables));
    }
}
