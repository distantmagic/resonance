---
collections: 
    - documents
layout: dm:document
parent: docs/features/timers/index
title: Tick Timer
description: >
    Learn how to schedule tasks that trigger every N seconds.
---

# Tick Timer

## Usage

For example:

```php
<?php

declare(strict_types=1);

use Distantmagic\Resonance\Attribute\ScheduledWithTickTimer;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\TickTimerJobInterface;
use Distantmagic\Resonance\SingletonCollection;

#[ScheduledWithTickTimer(5)]
#[Singleton(collection: SingletonCollection::TickTimerJob)]
readonly class EveryFiveSeconds extends TickTimerJobInterface
{
    public function onTimerTick(): void
    {
        swoole_error_log(SWOOLE_LOG_DEBUG, '5 seconds passed');
    }

    public function shouldRegister(): bool
    {
        return true;
    }
}
```
