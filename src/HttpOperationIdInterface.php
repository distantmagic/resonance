<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use ReflectionClass;

/**
 * Used to compose OpenAPI schemas.
 */
interface HttpOperationIdInterface
{
    /**
     * This string must be globally unique and human-readable
     */
    public function generateOperationId(
        RequestMethod $requestMethod,
        ReflectionClass $httpResponderReflectionClass,
    ): string;
}
