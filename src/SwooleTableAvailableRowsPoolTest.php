<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(SwooleTableAvailableRowsPool::class)]
final class SwooleTableAvailableRowsPoolTest extends TestCase
{
    public function test_address_is_trusted(): void
    {
        $availableRowsPool = new SwooleTableAvailableRowsPool(4);

        self::assertSame('0', $availableRowsPool->nextAvailableRow());
        self::assertSame('1', $availableRowsPool->nextAvailableRow());
        self::assertSame('2', $availableRowsPool->nextAvailableRow());
        self::assertSame('3', $availableRowsPool->nextAvailableRow());

        $availableRowsPool->freeAvailableRow('1');

        self::assertSame('1', $availableRowsPool->nextAvailableRow());

        $availableRowsPool->freeAvailableRow('3');

        self::assertSame('3', $availableRowsPool->nextAvailableRow());

        $availableRowsPool->freeAvailableRow('0');
        $availableRowsPool->freeAvailableRow('2');

        self::assertSame('2', $availableRowsPool->nextAvailableRow());
        self::assertSame('0', $availableRowsPool->nextAvailableRow());
    }
}
