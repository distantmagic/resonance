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
#[CoversClass(ObservableTaskTable::class)]
final class ObservableTaskTableTest extends TestCase
{
    protected function tearDown(): void
    {
        Event::wait();
    }

    public function test_address_is_trusted(): void
    {
        $observableTaskConfiguration = new ObservableTaskConfiguration(
            maxTasks: 4,
            serializedStatusSize: 32768,
        );

        $observableTaskTable = new ObservableTaskTable(
            observableTaskConfiguration: $observableTaskConfiguration,
            serializer: new Vanilla(),
        );

        $observableTask = new ObservableTask(static function () {
            yield new ObservableTaskStatusUpdate(
                ObservableTaskStatus::Running,
                'test',
            );
        });

        self::assertNull($observableTaskTable->getStatus('0'));

        $slotId = $observableTaskTable->observe($observableTask);

        self::assertSame('0', $slotId);

        $status = $observableTaskTable->getStatus($slotId);

        self::assertInstanceOf(ObservableTaskStatusUpdate::class, $status);
        self::assertSame(ObservableTaskStatus::Running, $status->status);
        self::assertSame('test', $status->data);
    }
}
