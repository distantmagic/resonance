<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use LogicException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

class HttpControllerMetadataException extends LogicException
{
    public function __construct(
        string $message,
        private readonly ReflectionFunction|ReflectionMethod $reflectionFunction,
        private readonly ?ReflectionParameter $parameter = null,
        private readonly ?ReflectionNamedType $type = null,
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
            ($this->reflectionFunction instanceof ReflectionMethod)
                ? $this->reflectionFunction->getDeclaringClass()->getName()
                : '-',
            $this->reflectionFunction->getName(),
        );

        if (!isset($this->parameter)) {
            return $ret;
        }

        return $ret.sprintf(
            '(%s$%s)',
            isset($this->type) ? $this->type->getName().' ' : '',
            $this->parameter->getName(),
        );
    }
}
