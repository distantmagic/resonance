<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\helpers;

use RuntimeException;

/**
 * @param callable() $callback
 */
function coroutineMustGo(callable $callback): int
{
    $cid = go($callback);

    if (!is_int($cid)) {
        throw new RuntimeException('Unable to start a coroutine');
    }

    return $cid;
}
