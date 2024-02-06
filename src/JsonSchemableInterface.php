<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;
use stdClass;

/**
 * @psalm-type PJsonSchema = stdClass|array{
 *     anyOf?: list<array>,
 *     const?: int|float|non-empty-string,
 *     default?: mixed,
 *     type?: non-empty-string|list<non-empty-string>,
 *     ...
 * }
 */
interface JsonSchemableInterface extends JsonSerializable
{
    /**
     * @return PJsonSchema
     */
    public function toJsonSchema(): array|object;
}
