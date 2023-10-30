---
collections: 
    - documents
layout: dm:document
parent: docs/features/index
title: Console
description: >
    Use Resonance's built-in console commands and learn how to add your own.
---

# Console

The console module gives you the ability to add your console commands to the 
Resonance application.

Resonance uses 
[Symfony Console Component](https://symfony.com/doc/current/components/console.html)
under the hood, with a few caveats.

Generally, you can follow Symfony's Console documentation, but there are a few 
differences (to integrate Symfony's Console component with Swoole 
and Resonance):

1. Commands are registered **only** by using 
    `Distantmagic\Resonance\Attribute\ConsoleCommand`
    attribute (instead of 
    `Symfony\Component\Console\Attribute\AsCommand`
    or by manually adding the command to the console application).
1. All commands are lazy-loaded by default - only their names and descriptions
    obtained from the `#[ConsoleCommand]` attribute are loaded immediately. 
    The framework instantiates a command only if you invoke it directly. 
    If you want commands to be loaded immediately, you should add 
    `#[Singleton]` attribute to the command - you shouldn't, however.
1. {{docs/features/dependency-injection/index}} container creates all the 
    commands and injects singletons into their constructor parameters.
1. Signal handling is *not* supported (this is probably going to change in the
    future).

# Usage

The basic console command may look as follows. It's registered automatically
during the application's bootstrapping phase:

```php
<?php

declare(strict_types=1);

namespace App\Commands;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[ConsoleCommand(name: 'hello')]
final class Hello extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Hello, world!');

        return Command::SUCCESS;
    }
}
```

Then you can use it:

```shell
$ php ./bin/resonance.php hello
Hello, world!
```
