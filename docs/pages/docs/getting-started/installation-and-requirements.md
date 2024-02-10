---
collections: 
    - documents
layout: dm:document
parent: docs/getting-started/index
title: Installation and Requirements
description: >
    Learn about the installation process and framework's requirements.
---

# Requirements

## PHP Version

At least `8.2`. Resonance extensively uses the latest PHP features.

## Required PHP Extensions

With those extensions, running the framework is not possible.

extension | symbol | why?
-|-|-
[Data Structures](https://www.php.net/manual/en/book.ds.php) | `ext-ds` | Data Structures are not only fast, but they have better memory management than the base PHP arrays, which is essential to create stable long-running applications.<br><br>You can learn more in this article: [Efficient data structures for PHP 7](https://medium.com/@rtheunissen/efficient-data-structures-for-php-7-9dda7af674cd)
[Sockets](https://www.php.net/manual/en/intro.sockets.php) | `ext-sockets` | Required by Swoole and other services.
[Swoole](https://www.swoole.com/) | `ext-swoole` | Provides asynchronous features to PHP.<br><br>Can also be replaced by it's fork - [OpenSwoole](https://openswoole.com/).
[uuid](https://pecl.php.net/package/uuid) | `ext-uuid` | UUID generation for session ids and such.

:::note
If you are using [Psalm](https://psalm.dev/) or [PHPStan](https://phpstan.org/) 
and you need types then you can install 
[php-ds/polyfill](https://github.com/php-ds/polyfill) for `php-ds` and
[swoole/ide-helper](https://github.com/swoole/ide-helper) for `swoole`.

`php-ds` polyfill is configured to use the native extension if it's installed,
so if you use both the extension and polyfill, it will effectively provide just
the types. Do not rely on polyfill instead of having the extensions installed,
because the polyfill won't grant you the memory management features, which
is the primary reason behind having the extension here.
:::

## Recommended PHP Extensions

Without the recommended extensions, some framework's features won't work at all 
or would operate at a degraded performance.

extension | symbol | why?
-|-|-
[Igbinary](https://www.php.net/manual/en/book.igbinary.php) | `ext-igbinary` | It's used to serialize and unserialize {{docs/features/http/sessions}}. It has a better memory footprint than a basic PHP `serialize` and allows to use `ext-ds` to store the session data.
[Intl](https://www.php.net/manual/en/book.intl.php) | `ext-intl` | Handles date formatting, helps with translations etc.
[Mailparse](https://www.php.net/manual/en/book.mailparse.php) | `ext-mailparse` | Only if you want to integrate with Postfix.
[OpenSSL](https://datatracker.ietf.org/doc/html/rfc7519) | `ext-openssl` | Handles security keys, especially useful for {{docs/features/security/oauth2/index}}.
[Readline](https://www.php.net/manual/en/book.readline.php) | `ext-readline` | Unlocks {{docs/features/console/index}} features, formatting, piping etc.
[Redis](https://github.com/phpredis/phpredis) | `ext-redis` | Redis to handle {{docs/features/http/sessions}}

# Installation

Preferably you should start the project by using 
[Composer's](https://getcomposer.org/) `create-project` command:

```shell
$ composer create-project distantmagic/resonance-project my-project
```

Alternatively you can manually clone the
[distantmagic/resonance-project](https://github.com/distantmagic/resonance-project)
repository and run `composer install` afterwards. The end result is going to be
identical as using the `create-project`.

# First Use

After installing the project, you should create the `config.ini` file 
(you can copy `config.ini.example` with it's default values) 
and then use `bin/resonance.php` as an entry 
point. After invoking `php bin/resonance.php` in the shell, you should see
the list of available commands:

```shell
Resonance

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
  completion                Dump the shell completion script
  help                      Display help for a command
  list                      List commands
 generate
  generate:http-controller  Generate http-controller
  generate:http-responder   Generate http-responder
 static-pages
  static-pages:build        Generate static pages
```

`php bin/resonance.php serve` should start the HTTP server. If you need to, 
you can generate the {{docs/extras/ssl-certificate-for-local-development/index}}.
