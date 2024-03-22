#!/usr/bin/env php
<?php

declare(strict_types=1);

if (!version_compare(PHP_VERSION, PHP_VERSION, '=')) {
    fwrite(
        STDERR,
        sprintf(
            '%s declares an invalid value for PHP_VERSION.'.PHP_EOL
            .'This breaks fundamental functionality such as version_compare().'.PHP_EOL
            .'Please use a different PHP interpreter.'.PHP_EOL,
            PHP_BINARY
        )
    );

    exit(1);
}

if (version_compare('8.2.0', PHP_VERSION, '>')) {
    fwrite(
        STDERR,
        sprintf(
            'This version of PHPUnit requires PHP >= 8.2.'.PHP_EOL
            .'You are using PHP %s (%s).'.PHP_EOL,
            PHP_VERSION,
            PHP_BINARY
        )
    );

    exit(1);
}

if (!ini_get('date.timezone')) {
    ini_set('date.timezone', 'UTC');
}

if (isset($GLOBALS['_composer_autoload_path'])) {
    define('PHPUNIT_COMPOSER_INSTALL', $GLOBALS['_composer_autoload_path']);

    unset($GLOBALS['_composer_autoload_path']);
} else {
    foreach ([__DIR__.'/../../autoload.php', __DIR__.'/../vendor/autoload.php', __DIR__.'/vendor/autoload.php'] as $file) {
        if (file_exists($file)) {
            define('PHPUNIT_COMPOSER_INSTALL', $file);

            break;
        }
    }

    unset($file);
}

if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
    fwrite(
        STDERR,
        'You need to set up the project dependencies using Composer:'.PHP_EOL.PHP_EOL
        .'    composer install'.PHP_EOL.PHP_EOL
        .'You can learn all about Composer on https://getcomposer.org/.'.PHP_EOL
    );

    exit(1);
}

require PHPUNIT_COMPOSER_INSTALL;

$requiredExtensions = ['dom', 'json', 'libxml', 'mbstring', 'swoole', 'tokenizer', 'xml', 'xmlwriter'];

$unavailableExtensions = array_filter(
    $requiredExtensions,
    static function ($extension) {
        return !extension_loaded($extension);
    }
);

// Workaround for https://github.com/sebastianbergmann/phpunit/issues/5662
if (!function_exists('ctype_alnum')) {
    $unavailableExtensions[] = 'ctype';
}

if ([] !== $unavailableExtensions) {
    fwrite(
        STDERR,
        sprintf(
            'PHPUnit requires the "%s" extensions, but the "%s" %s not available.'.PHP_EOL,
            implode('", "', $requiredExtensions),
            implode('", "', $unavailableExtensions),
            1 === count($unavailableExtensions) ? 'extension is' : 'extensions are'
        )
    );

    exit(1);
}

unset($requiredExtensions, $unavailableExtensions);

Swoole\Coroutine::set(
    [
        'exit_condition' => static function (): bool {
            return 0 === Swoole\Coroutine::stats()['coroutine_num'];
        },
    ]
);

$code = 0;

Swoole\Coroutine\run(static function () use (&$code): void {
    $code = (new PHPUnit\TextUI\Application())->run($_SERVER['argv']);
    Swoole\Timer::clearAll();
});

exit($code);
