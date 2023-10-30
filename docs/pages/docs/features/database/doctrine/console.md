---
collections: 
    - documents
layout: dm:document
next: docs/features/database/doctrine/entities
parent: docs/features/database/doctrine/index
title: Console
description: >
    Learn how to use Doctrine's console tool with Resonance
---

# Console

To make Doctrine use Resonance's features, you need to create a dedicated
configuration file that uses entity maanager specific to Resonance:

```php file:bin/doctrine.php
<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

defined('DM_ROOT') or exit('Configuration is not loaded.');

use Distantmagic\Resonance\DoctrineConsoleRunner;

DoctrineConsoleRunner::run();
```

Then, after invoking `php doctrine.php` you should see Donctrine's commands:

```shell
$ php ./bin/doctrine.php
Doctrine Command Line Interface 2.16.2.0

Usage:
  command [options] [arguments]

Options:
  -h, --help            Display help for the given command. When no command is given display help for the list command
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi|--no-ansi  Force (or disable --no-ansi) ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  completion                         Dump the shell completion script
  help                               Display help for a command
  list                               List commands
 dbal
  dbal:reserved-words                Checks if the current database contains identifiers that are reserved.
  dbal:run-sql                       Executes arbitrary SQL directly from the command line.
(...)
```
