---
collections: 
    - documents
layout: dm:document
parent: docs/features/index
title: Swoole Server Tasks
description: >
    Learn how to schedule asynchronous tasks in the Swoole web server.
---

# Swoole Server Tasks

There are times when you might not want to perform certain operations while 
handling HTTP requests with {{docs/features/http/responders}}, for example:
sending {{docs/features/mail/index}}, storing uploaded files etc.

Server tasks are somewhat in between the message brokers, like 
[RabbitMQ](https://rabbitmq.com/) and just using coroutines.
Resonance runs tasks on the same server that issued them, but they are also 
executed in a separate process, in parallel to other tasks that the server
could be working on at that moment.

# Usage

:::note
`Distantmagic\Resonance\SwooleTaskServerMessageBus` implements 
`Symfony\Component\Messenger\MessageBusInterface`, making it compatible 
with 
[Symfony's Messenger Component](https://symfony.com/doc/current/components/messenger.html)
:::

:::caution
Swoole Server Tasks will only work when the {{docs/features/http/server}} is 
running.
:::

## Configuration

You can optionally configure the number of worker processes that Resonance will 
spawn when the {{docs/features/http/server}} starts:

```ini file:config.ini
[swoole]
; ...
task_worker_num = 4
; ...
```

## Dispatching Tasks

Tasks can be of any type. You do not have to extend any other class or 
implement any interface. 

You need to use `SwooleTaskServerMessageBus` to dispatch tasks. For example:

```php file:app/MyTask.php
<?php

namespace App;

readonly class MyTask
{
    public function __construct(public string $hello) {}
}
```

```php file:app/MyClass.php
<?php

namespace App;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\WantsFeature;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\SwooleTaskServerMessageBus;

#[Singleton]
#[WantsFeature(Feature::SwooleTaskServer)]
readonly class MyClass
{
    public function __construct(
        private SwooleTaskServerMessageBus $swooleTaskServerMessageBus
    ) {}

    public function doSomething(): void
    {
        $this->swooleTaskServerMessageBus->dispatch(new MyTask('world'));
    }
}
```

## Handling Tasks

You need to register a `Singleton` in the 
{{docs/features/dependency-injection/index}} container. It needs to belong to
`ServerTaskHandler` collection.

`HandlesServerTask` attribute indicated which task the given handler can 
process. Task handler needs to either implement the
`Distantmagic\Resonance\ServerTaskHandlerInterface` or extend
`Distantmagic\Resonance\ServerTaskHandler`.

Resonance executes task handlers in a separate process, so unless you exhaust 
the entire server resources, it won't affect the HTTP response times:

```php file:app/MyTaskHandler.php
<?php

namespace App;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\HandlesServerTask;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\ServerTaskHandler;
use Distantmagic\Resonance\SingletonCollection;
use Psr\Log\LoggerInterface;

/**
 * @template-extends ServerTaskHandler<MyTask>
 */
#[GrantsFeature(Feature::SwooleTaskServer)]
#[HandlesServerTask(MyTask::class)]
#[Singleton(collection: SingletonCollection::ServerTaskHandler)]
readonly class MyTaskHandler extends ServerTaskHandler
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    /**
     * @var MyTask $serverTask
     */
    public function handleServerTask(object $serverTask): void
    {
        $this->logger->info('Hello '.$serverTask->hi);
    }
}
```
