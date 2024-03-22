<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use PHPUnit\Framework\TestCase;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use Swoole\Event;
use Swoole\Table;
use Swoole\Timer;

/**
 * @internal
 */
final class SwooleCrashTest extends TestCase
{
    protected function tearDown(): void
    {
        Event::wait();
    }

    public function test_channel(): void
    {
        $channel = new Channel(1);
        $channel->close();
    }

    public function test_code_executes_after_timeout(): void
    {
        // just the Event::wait
    }

    public function test_coroutine_with_timer(): void
    {
        SwooleCoroutineHelper::mustGo(static function () {
            Timer::after(10, static function () {
                // timer
            });
        });
    }

    public function test_just_coroutine(): void
    {
        SwooleCoroutineHelper::mustGo(static function () {
            // just the coroutine
        });
    }

    public function test_table(): void
    {
        $table = new Table(20000);
        $table->column('status', Table::TYPE_STRING, 30000);
        $table->create();
    }

    public function test_table_small(): void
    {
        $table = new Table(100);
        $table->column('status', Table::TYPE_STRING, 300);
        $table->create();
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
    }
}
