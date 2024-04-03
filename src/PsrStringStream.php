<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\StreamInterface;
use Stringable;

readonly class PsrStringStream implements StreamInterface
{
    private string $content;

    public function __construct(string|Stringable $content)
    {
        $this->content = (string) $content;
    }

    public function __toString(): string
    {
        return $this->content;
    }

    public function close(): void {}

    public function detach() {}

    public function eof(): bool
    {
        return true;
    }

    public function getContents(): string
    {
        return $this->content;
    }

    public function getMetadata($key = null)
    {
        return null;
    }

    public function getSize(): ?int
    {
        return strlen($this->content);
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function isSeekable(): bool
    {
        return false;
    }

    public function isWritable(): bool
    {
        return false;
    }

    public function read($length): string
    {
        return '';
    }

    public function rewind(): void {}

    public function seek($offset, $whence = SEEK_SET): void {}

    public function tell(): int
    {
        return 0;
    }

    public function write($string): int
    {
        return 0;
    }
}
