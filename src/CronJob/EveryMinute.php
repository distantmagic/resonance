<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\CronJob;

use Distantmagic\Resonance\Attribute\ScheduledWithCron;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\CronJob;
use Distantmagic\Resonance\SingletonCollection;

#[ScheduledWithCron('* * * * *')]
#[Singleton(collection: SingletonCollection::CronJob)]
readonly class EveryMinute extends CronJob
{
    public function onCronTick(): void
    {
        /**
         * @psalm-suppress UnusedFunctionCall
         */
        swoole_error_log(SWOOLE_LOG_DEBUG, __METHOD__);
    }
}
