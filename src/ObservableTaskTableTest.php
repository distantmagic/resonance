<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Serializer\Vanilla;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\WaitGroup;
use Swoole\Event;

/**
 * @internal
 */
#[CoversClass(ObservableTaskTable::class)]
final class ObservableTaskTableTest extends TestCase
{
    private ?ObservableTaskConfiguration $observableTaskConfiguration = null;
    private ?ObservableTaskTable $observableTaskTable = null;

    protected function setUp(): void
    {
        $this->observableTaskConfiguration = new ObservableTaskConfiguration(
            maxTasks: 4,
            serializedStatusSize: 32768,
        );

        $this->observableTaskTable = new ObservableTaskTable(
            observableTaskConfiguration: $this->observableTaskConfiguration,
            serializer: new Vanilla(),
        );
    }

    protected function tearDown(): void
    {
        Event::wait();
    }

    public function test_channel_is_observed(): void
    {
        SwooleCoroutineHelper::mustRun(function () {
            $channel = new Channel();
            $wg = new WaitGroup();

            $this->observableTaskTable?->observableChannels->add($channel);

            $observableTask = new ObservableTask(static function () {
                yield new ObservableTaskStatusUpdate(
                    ObservableTaskStatus::Running,
                    'test1',
                );

                yield new ObservableTaskStatusUpdate(
                    ObservableTaskStatus::Finished,
                    'test2',
                );
            });

            $wg->add();

            SwooleCoroutineHelper::mustGo(static function () use ($channel, $wg) {
                Coroutine::defer(static function () use ($wg) {
                    $wg->done();
                });

                $status1 = $channel->pop();

                self::assertInstanceOf(ObservableTaskSlotStatusUpdate::class, $status1);
                self::assertSame(ObservableTaskStatus::Running, $status1->observableTaskStatusUpdate->status);

                $status2 = $channel->pop();

                self::assertInstanceOf(ObservableTaskSlotStatusUpdate::class, $status2);
                self::assertSame(ObservableTaskStatus::Finished, $status2->observableTaskStatusUpdate->status);
            });

            $this->observableTaskTable?->observe($observableTask);

            $wg->wait();

            $this->observableTaskTable?->observableChannels->remove($channel);
        });
    }

    public function test_task_is_observed(): void
    {
        $observableTask = new ObservableTask(static function () {
            yield new ObservableTaskStatusUpdate(
                ObservableTaskStatus::Running,
                'test',
            );
        });

        self::assertNull($this->observableTaskTable?->getStatus('0'));

        $slotId = $this->observableTaskTable?->observe($observableTask);

        self::assertSame('0', $slotId);

        $status = $this->observableTaskTable?->getStatus($slotId);

        self::assertInstanceOf(ObservableTaskStatusUpdate::class, $status);
        self::assertSame(ObservableTaskStatus::Running, $status->status);
        self::assertSame('test', $status->data);
    }
}
