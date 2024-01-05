<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpOperationId;

use Distantmagic\Resonance\HttpOperationId;
use Distantmagic\Resonance\RequestMethod;
use ReflectionClass;

readonly class StaticString extends HttpOperationId
{
    public function __construct(private string $staticString) {}

    public function generateOperationId(
        RequestMethod $requestMethod,
        ReflectionClass $httpResponderReflectionClass,
    ): string {
        return $this->staticString;
    }
}
