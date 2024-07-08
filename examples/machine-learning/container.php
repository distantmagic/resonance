<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

defined('DM_ROOT') or exit('Configuration is not loaded.');

use Distantmagic\Resonance\DependencyInjectionContainer;
use Swoole\Runtime;

Runtime::enableCoroutine(SWOOLE_HOOK_ALL);

$container = new DependencyInjectionContainer();
$container->phpProjectFiles->indexDirectory(DM_RESONANCE_ROOT);
$container->phpProjectFiles->indexDirectory(DM_APP_ROOT);
$container->registerSingletons();

return $container;
