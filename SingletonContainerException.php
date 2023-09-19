<?php

declare(strict_types=1);

namespace Resonance;

use LogicException;
use Psr\Container\ContainerExceptionInterface;

class SingletonContainerException extends LogicException implements ContainerExceptionInterface {}
