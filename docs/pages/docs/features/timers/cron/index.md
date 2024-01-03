---
collections: 
    - documents
layout: dm:document
parent: docs/features/timers/index
title: CRON
description: >
    Learn how to schedule tasks with CRON notation.
---

# CRON

## Usage

### Running Scheduler

You need to invoke the `cron` command:

```php
$ php bin/resonance.php cron
```

That command should be a long-running processs, it *MUST NOT* be executed every 
minute, because Resonance has it's own built-in CRON scheduler.

### Implementing CRON Jobs

Your class has to implement `CronJobInterface` (or extend `CronJob`) and have 
`ScheduledWithCron` attribute.

It also needs to belong to the `CronJob` collection.

For example:

```php
<?php

declare(strict_types=1);

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
        swoole_error_log(SWOOLE_LOG_DEBUG, 'minute passed');
    }
}
```
