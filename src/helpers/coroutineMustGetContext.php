<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\helpers;

use RuntimeException;
use Swoole\Coroutine;
use Swoole\Coroutine\Context;

function coroutineMustGetContext(): Context
{
    /**
     * @var null|Context
     */
    $context = Coroutine::getContext();

    if (is_null($context)) {
        throw new RuntimeException('Unable to get coroutine context');
    }

    return $context;
}
