<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\RequiresBackendDriver;
use Distantmagic\Resonance\Attribute\Singleton;
use RuntimeException;
use Swoole\Timer;

#[RequiresBackendDriver(BackendDriver::Swoole)]
#[Singleton]
class TickTimerScheduler
{
    private int $currentTick = 0;
    private ?int $tickTimerId = null;

    public function __construct(private readonly TickTimerJobAggregate $tickTimerJobAggregate) {}

    public function shouldRegister(): bool
    {
        return true;
    }

    public function start(): void
    {
        $this->scheduleTick();
    }

    public function stop(): void
    {
        if (is_null($this->tickTimerId)) {
            return;
        }

        Timer::clear($this->tickTimerId);

        $this->tickTimerId = null;
    }

    private function onTick(): void
    {
        ++$this->currentTick;

        foreach ($this->tickTimerJobAggregate->tickTimerJobs as $tickTimerRegisteredJob) {
            if (0 === ($this->currentTick % $tickTimerRegisteredJob->interval)) {
                $tickTimerRegisteredJob->tickTimerJob->onTimerTick();
            }
        }
    }

    private function scheduleTick(): void
    {
        if (!is_null($this->tickTimerId)) {
            throw new RuntimeException('Tick is already scheduled');
        }

        /**
         * @var int
         */
        $this->tickTimerId = Timer::tick(1000, $this->onTick(...));
    }
}
