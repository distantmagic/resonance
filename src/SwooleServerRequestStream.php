<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Swoole\Http\Request;

readonly class SwooleServerRequestStream implements StreamInterface
{
    private string $contents;

    public function __construct(Request $request)
    {
        $content = $request->getContent();

        if (!is_string($content)) {
            throw new InvalidArgumentException('Request content is not a string');
        }

        $this->contents = $content;
    }

    public function __toString(): string
    {
        return $this->contents;
    }

    public function close(): void {}

    public function detach() {}

    public function eof(): bool
    {
        return true;
    }

    public function getContents(): string
    {
        return $this->contents;
    }

    public function getMetadata($key = null)
    {
        return null;
    }

    public function getSize(): ?int
    {
        return strlen($this->contents);
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
