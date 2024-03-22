<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swoole\Event;

/**
 * @internal
 */
final class SwooleCrashTest extends TestCase
{
    public function test_code_executes_after_timeout(): void
    {
        $before = microtime(true);

        $timeout = new SwooleTimeout(static function () use ($before) {
            $after = microtime(true);

            self::assertGreaterThan(0.03, $after - $before);
            self::assertLessThan(0.035, $after - $before);
        });

        $timeout->setTimeout(0.03);

        Event::wait();
    }

    public function test_task_is_rescheduled(): void
    {
        $before = microtime(true);

        $timeout = new SwooleTimeout(static function () use ($before) {
            $after = microtime(true);

            self::assertGreaterThan(0.03, $after - $before);
            self::assertLessThan(0.035, $after - $before);
        });

        $timeout->setTimeout(0.02)->reschedule(0.03);

        Event::wait();
    }
}
