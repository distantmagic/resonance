---
collections:
    - documents
layout: dm:document
parent: docs/features/database/index
title: Migrations
description: >
    Integrate with a migration tool to run database migrations.
---

# Migrations

## Using Phinx

[Phinx](https://phinx.org/) is a popular database migration tool. You can
use any migration tool you want. This documentation page provides an example 
of how to integrate Phinx specifically.

Why Phinx? It's simple, does the job, and is well-maintained.

### Installation

It's best to install phinx in a directory separated from your application,
with its own `composer.json`, just to avoid getting into dependency hell.

For example, keep your application files in the `app` folder and Phinx in
the `tools/phinx` directory. You should still commit everything in the 
repo and branch:

```shell
$ composer require --working-dir=tools/phinx robmorgan/phinx
```

You can also try using [Phive](https://phar.io/) to install Phinx instead.

### Configuration

Phinx uses `phinx.php` configuration file. You can use the 
{{docs/features/dependency-injection/index}} container to fetch the 
database configuration:

```php file:phinx.php
<?php

require_once __DIR__.'/vendor/autoload.php';

defined('DM_ROOT') or exit('Configuration is not loaded.');

use Distantmagic\Resonance\DatabaseConfiguration;
use Distantmagic\Resonance\DependencyInjectionContainer;
use Swoole\Runtime;

$container = new DependencyInjectionContainer();
$container->phpProjectFiles->indexDirectory(DM_RESONANCE_ROOT);
$container->phpProjectFiles->indexDirectory(DM_APP_ROOT);
$container->registerSingletons();

return $container->call(static function (DatabaseConfiguration $databaseConfiguration) {
    $connectionPoolConfiguration = $databaseConfiguration
        ->connectionPoolConfiguration
        ->get('default')
    ;

    return [
        'paths' => [
            'migrations' => '%%PHINX_CONFIG_DIR%%/migrations',
            'seeds' => '%%PHINX_CONFIG_DIR%%/seeds',
        ],
        'environments' => [
            'default_migration_table' => 'phinxlog',
            'default_environment' => 'mysql',
            'mysql' => [
                'adapter' => 'mysql',
                'host' => $connectionPoolConfiguration->host,
                'name' => $connectionPoolConfiguration->database,
                'user' => $connectionPoolConfiguration->username,
                'pass' => $connectionPoolConfiguration->password,
                'port' => $connectionPoolConfiguration->port,
                'charset' => 'utf8',
            ],
        ],
        'version_order' => 'creation',
    ];
});
```

After you set up the configuration file, you can follow Phinx's instructions
on how to set up and invoke migrations.
