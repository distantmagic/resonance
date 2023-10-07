<?php

declare(strict_types=1);

namespace Resonance;

/**
 * @template TResult
 */
interface DatabaseQueryInterface
{
    /**
     * I wish PHP had generics.
     *
     * @return TResult
     */
    public function execute();

    public function isIterable(): bool;
}
