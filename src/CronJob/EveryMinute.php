<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\CronJob;

use Distantmagic\Resonance\Attribute\ScheduledWithCron;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\CronJobInterface;
use Distantmagic\Resonance\SingletonCollection;

#[ScheduledWithCron('* * * * *')]
#[Singleton(collection: SingletonCollection::CronJob)]
readonly class EveryMinute implements CronJobInterface
{
    public function onCronTick(): void
    {
        /**
         * @psalm-suppress InvalidArgument
         * @psalm-suppress UnusedFunctionCall
         */
        swoole_error_log(SWOOLE_DEBUG, __METHOD__);
    }
}
