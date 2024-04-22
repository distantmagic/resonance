<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\helpers;

use Generator;

/**
 * @psalm-suppress UnusedForeachValue we need to consume the iterator
 *
 * @template TReturn
 *
 * @param Generator<mixed,mixed,mixed,TReturn> $generator
 *
 * @return TReturn
 */
function generatorGetReturn(Generator $generator): mixed
{
    /**
     * @var mixed $value explicitly mixed for typechecks
     */
    foreach ($generator as $value) {
    }

    return $generator->getReturn();
}
