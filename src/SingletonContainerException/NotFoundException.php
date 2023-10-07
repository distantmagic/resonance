<?php

declare(strict_types=1);

namespace Resonance\SingletonContainerException;

use Psr\Container\NotFoundExceptionInterface;
use Resonance\SingletonContainerException;

class NotFoundException extends SingletonContainerException implements NotFoundExceptionInterface {}
