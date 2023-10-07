<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonContainerException;

use Psr\Container\NotFoundExceptionInterface;
use Distantmagic\Resonance\SingletonContainerException;

class NotFoundException extends SingletonContainerException implements NotFoundExceptionInterface {}
