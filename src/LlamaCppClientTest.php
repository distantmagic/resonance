<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(LlamaCppClient::class)]
#[Group('llamacpp')]
final class LlamaCppClientTest extends TestCase
{
    use TestsDependencyInectionContainerTrait;

    public function test_request_header_is_parsed(): void
    {
        $llamaCppClient = self::$container->make(LlamaCppClient::class);

        self::assertSame(LlamaCppHealthStatus::Ok, $llamaCppClient->getHealth());
    }
}
