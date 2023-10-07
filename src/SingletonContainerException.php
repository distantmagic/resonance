<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use LogicException;
use Psr\Container\ContainerExceptionInterface;

class SingletonContainerException extends LogicException implements ContainerExceptionInterface {}
