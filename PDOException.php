<?php

declare(strict_types=1);

namespace Resonance;

use RuntimeException;
use Throwable;

class PDOException extends RuntimeException
{
    /**
     * @param array{0: null|string, 1: null|int, 2: null|string, 3?: mixed, 4?: mixed}|mixed $errorInfo
     */
    public function __construct(mixed $errorInfo, ?Throwable $previous = null)
    {
        parent::__construct(print_r($errorInfo, true), 0, $previous);
    }
}
