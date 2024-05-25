<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/constants.php';

defined('DM_ROOT') or exit('Configuration is not loaded.');

use Distantmagic\Resonance\CoroutineDriver\Swoole;
use Distantmagic\Resonance\DependencyInjectionContainer;

$coroutineDriver = new Swoole();
$coroutineDriver->init();

$container = new DependencyInjectionContainer(
    coroutineDriver: $coroutineDriver,
);
$container->phpProjectFiles->indexDirectory(DM_RESONANCE_ROOT);
$container->phpProjectFiles->indexDirectory(DM_APP_ROOT);
$container->registerSingletons();

return $container;
