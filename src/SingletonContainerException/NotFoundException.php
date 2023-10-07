<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonContainerException;

use Distantmagic\Resonance\SingletonContainerException;
use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends SingletonContainerException implements NotFoundExceptionInterface {}
