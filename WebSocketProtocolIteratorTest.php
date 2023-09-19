<?php

declare(strict_types=1);

namespace Resonance;

use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 *
 * @internal
 */
final class WebSocketProtocolIteratorTest extends TestCase
{
    public function test_iterates_over_protocol_values(): void
    {
        $iter = new WebSocketProtocolIterator(' foo,         dm-rpc,bar, baz');
        $values = iterator_to_array($iter);

        self::assertCount(1, $values);
        self::assertContainsOnlyInstancesOf(WebSocketProtocol::class, $values);
        self::assertEquals(WebSocketProtocol::RPC, $values[0]);
    }
}
