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

Since Resonance supports {{docs/features/database/doctrine/index}}, we 
recommend using Doctrine's migration tool.

If you want to use some different tools, like, for example, 
[Phinx](https://phinx.org/), you can also learn here how to do so.

## Using Doctrine

You need to 
[install doctrine/migrations](https://www.doctrine-project.org/projects/migrations.html) 
first, by following their documentation.

To configure Doctrine's migration configuration, you need to create 
`migrations.php` in the root of your project:

```php file:migrations.php
<?php

return [
    // leave this file empty if you are OK with Doctrine's defaults
    // see more at:
    // https://www.doctrine-project.org/projects/doctrine-migrations/en/3.6/reference/configuration.html
];
```

Then, the simplest way to configure Doctrine connection parameters is to create 
`migrations-db.php` file in the project's root folder and use Resonance's
{{docs/features/dependency-injection/index}} to obtain the Database 
Configuration:

```php file:migrations-db.php
<?php

require_once __DIR__.'/vendor/autoload.php';

defined('DM_ROOT') or exit('Configuration is not loaded.');

use Distantmagic\Resonance\DatabaseConfiguration;
use Distantmagic\Resonance\DatabaseConnectionPoolDriverName;
use Distantmagic\Resonance\DependencyInjectionContainer;

return DependencyInjectionContainer::boot(static function (
    DatabaseConfiguration $databaseConfiguration,
) {
    $connectionPoolConfiguration = $databaseConfiguration
        ->connectionPoolConfiguration
        ->get('default')
    ;

    return [
        'dbname' => $connectionPoolConfiguration->database,
        'user' => $connectionPoolConfiguration->username,
        'password' => $connectionPoolConfiguration->password,
        'host' => $connectionPoolConfiguration->host,
        'driver' => match ($connectionPoolConfiguration->driver) {
            DatabaseConnectionPoolDriverName::MySQL => 'pdo_mysql',
            DatabaseConnectionPoolDriverName::PostgreSQL => 'pdo_pgsql',
            DatabaseConnectionPoolDriverName::SQLite => 'pdo_sqlite',
        },
    ];
});
```

Then you should be able to use `php ./vendor/bin/doctrine-migrations` command
line tool to manage your migrations.

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

return DependencyInjectionContainer::boot(static function (
    DatabaseConfiguration $databaseConfiguration,
) {
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
                'adapter' => $connectionPoolConfiguration->driver->value,
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
