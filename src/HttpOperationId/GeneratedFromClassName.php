<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpOperationId;

use Distantmagic\Resonance\HttpOperationId;
use Distantmagic\Resonance\RequestMethod;
use ReflectionClass;

readonly class GeneratedFromClassName extends HttpOperationId
{
    public function generateOperationId(
        RequestMethod $requestMethod,
        ReflectionClass $httpResponderReflectionClass,
    ): string {
        return sprintf(
            '%s%s',
            ucfirst(strtolower($requestMethod->value)),
            str_replace('\\', '', $httpResponderReflectionClass->getName()),
        );
    }
}
