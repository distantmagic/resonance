<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use LogicException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

class HttpControllerMetadataException extends LogicException
{
    public function __construct(
        string $message,
        private ReflectionMethod $reflectionMethod,
        private ?ReflectionParameter $parameter = null,
        private ?ReflectionNamedType $type = null,
    ) {
        parent::__construct(sprintf(
            '%s in %s',
            $message,
            $this->createDebugPath(),
        ));
    }

    private function createDebugPath(): string
    {
        $ret = sprintf(
            '%s@%s',
            $this->reflectionMethod->getDeclaringClass()->getName(),
            $this->reflectionMethod->getName(),
        );

        if (!isset($this->parameter)) {
            return $ret;
        }

        $ret .= sprintf(
            '(%s$%s)',
            isset($this->type) ? $this->type->getName().' ' : '',
            $this->parameter->getName(),
        );

        return $ret;
    }
}
