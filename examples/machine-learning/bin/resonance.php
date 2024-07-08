<?php

declare(strict_types=1);

use Distantmagic\Resonance\ConsoleApplication;

$container = require_once __DIR__.'/../container.php';

exit($container->make(ConsoleApplication::class)->run());
