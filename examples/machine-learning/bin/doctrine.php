<?php

declare(strict_types=1);

use Distantmagic\Resonance\DoctrineConsoleRunner;

$container = require_once __DIR__.'/../container.php';

exit(DoctrineConsoleRunner::run($container));
