<?php

declare(strict_types=1);

namespace Resonance;

use ArrayAccess;
use LogicException;
use ReturnTypeWillChange;

/**
 * @template-implements ArrayAccess<string, string>
 */
readonly class ErrorBag implements ArrayAccess
{
    /**
     * @param array<string, string> $errors
     */
    public function __construct(private array $errors) {}

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->errors[$offset]);
    }

    #[ReturnTypeWillChange]
    public function offsetGet(mixed $offset): ?string
    {
        if (!$this->offsetExists($offset)) {
            return null;
        }

        return $this->errors[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new LogicException('ErrorBag is read only');
    }

    public function offsetUnset(mixed $offset): never
    {
        throw new LogicException('ErrorBag is read only');
    }
}
