<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(OAuth2ScopePatternChunk::class)]
final class OAuth2ScopePatternChunkTest extends TestCase
{
    public function test_is_no_variable(): void
    {
        $pattern = new OAuth2ScopePatternChunk('foo');

        self::assertFalse($pattern->isVariable());
        self::assertSame('foo', $pattern->basename);
    }

    public function test_is_variable(): void
    {
        $pattern = new OAuth2ScopePatternChunk('{foo}');

        self::assertTrue($pattern->isVariable());
        self::assertSame('foo', $pattern->basename);
    }
}
