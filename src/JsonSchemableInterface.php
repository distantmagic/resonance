<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use stdClass;

/**
 * @psalm-type PJsonSchema = stdClass|array{
 *     const?: int|float|non-empty-string,
 *     default?: mixed,
 *     type?: non-empty-string|list<non-empty-string>,
 *     ...
 * }
 */
interface JsonSchemableInterface
{
    /**
     * @return PJsonSchema
     */
    public function toJsonSchema(): array|object;
}
