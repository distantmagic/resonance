<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 *
 * @internal
 */
final class ArrayFlattenIteratorTest extends TestCase
{
    public function test_dotpaths_are_generated(): void
    {
        $iter = new ArrayFlattenIterator([
            'foo' => 'bar',
            'baz' => [
                'booz',
                'wooz' => [
                    'fooz' => 'gooz',
                ],
            ],
        ]);

        self::assertEquals([
            'foo' => 'bar',
            'baz.0' => 'booz',
            'baz.wooz.fooz' => 'gooz',
        ], iterator_to_array($iter));
    }
}
