<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use PHPUnit\Event\TestRunner\Finished;
use PHPUnit\Event\TestRunner\FinishedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade as EventFacade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use Swoole\Coroutine;
use Swoole\Timer;

final class PHPUnitSwooleCoroutineExtension implements Extension
{
    public function bootstrap(Configuration $configuration, EventFacade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscriber(new class($this) implements FinishedSubscriber {
            public function __construct(private PHPUnitSwooleCoroutineExtension $thisClass) {}

            public function notify(Finished $event): void
            {
                $this->thisClass->executeAfterLastTest();
            }
        });
    }

    public function executeAfterLastTest(): void
    {
        while (Coroutine::stats()['coroutine_num'] > 1) {
            Coroutine::sleep(0.1);
        }

        Timer::clearAll();
    }
}
