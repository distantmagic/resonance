<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\App;

require_once __DIR__.'/../constants.php';
require_once __DIR__.'/../vendor/autoload.php';

use Distantmagic\Resonance\ConsoleApplication;
use Distantmagic\Resonance\DependencyInjectionContainer;
use Swoole\Runtime;

Runtime::enableCoroutine(SWOOLE_HOOK_ALL);

$container = new DependencyInjectionContainer();
$container->phpProjectFiles->indexDirectory(DM_RESONANCE_ROOT);
$container->phpProjectFiles->indexDirectory(DM_APP_ROOT);
$container->registerSingletons();

exit($container->make(ConsoleApplication::class)->run());
