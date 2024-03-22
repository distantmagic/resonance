<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Serializer\Vanilla;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swoole\Event;

/**
 * @internal
 */
#[CoversClass(ObservableTaskTableSlotStatusUpdateIterator::class)]
final class ObservableTaskTableSlotStatusUpdateIteratorTest extends TestCase
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

            SwooleCoroutineHelper::mustGo(function () {
                self::assertNotNull($this->observableTaskTable);

                $iterator = new ObservableTaskTableSlotStatusUpdateIterator($this->observableTaskTable);

                foreach ($iterator as $statusUpdate) {
                    self::assertInstanceOf(ObservableTaskSlotStatusUpdate::class, $statusUpdate);
                    self::assertEquals('0', $statusUpdate->slotId);

                    if (ObservableTaskStatus::Finished === $statusUpdate->observableTaskStatusUpdate->status) {
                        self::assertEquals('test2', $statusUpdate->observableTaskStatusUpdate->data);

                        break;
                    }

                    self::assertEquals('test1', $statusUpdate->observableTaskStatusUpdate->data);
                }
            });

            $this->observableTaskTable?->observe($observableTask);
        });
    }
}
