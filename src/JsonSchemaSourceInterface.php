<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface JsonSchemaSourceInterface
{
    public function getSchema(): JsonSchema;
}
