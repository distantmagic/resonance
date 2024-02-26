<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\PsrMessage;

use Distantmagic\Resonance\PsrMessage;
use Distantmagic\Resonance\PsrStringStream;
use Ds\Map;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Swoole\Http\Response;

readonly class SwooleServerResponse extends PsrMessage implements ResponseInterface
{
    /**
     * @param Map<string,array<string>> $headers
     */
    public function __construct(
        private Response $response,
        private StreamInterface $body = new PsrStringStream(''),
        private int $code = 200,
        private Map $headers = new Map(),
        private string $protocol = '1.1',
        private string $reasonPhrase = '',
        public int $version = 0,
    ) {}

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function getHeader(string $name): array
    {
        return $this->headers->get($this->normalizeHeaderName($name), []);
    }

    public function getHeaderLine(string $name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    public function getHeaders(): array
    {
        return $this->headers->toArray();
    }

    public function getProtocolVersion(): never
    {
        $this->throwWriteOnly();
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    public function getStatusCode(): int
    {
        return $this->code;
    }

    public function hasHeader(string $name): bool
    {
        return $this->headers->hasKey($this->normalizeHeaderName($name));
    }

    public function withAddedHeader(string $name, $value): static
    {
        $headersCopy = $this->headers->copy();
        $headersCopy->put(
            $this->normalizeHeaderName($name),
            array_merge($this->getHeader($name), (array) $value),
        );

        return $this->copyWithHeaders($headersCopy);
    }

    public function withBody(StreamInterface $body): self
    {
        /**
         * @var static
         */
        return new self(
            $this->response,
            $body,
            $this->code,
            $this->headers,
            $this->protocol,
            $this->reasonPhrase,
            $this->version + 1,
        );
    }

    public function withHeader(string $name, $value): static
    {
        $headersCopy = $this->headers->copy();
        $headersCopy->put(
            $this->normalizeHeaderName($name),
            (array) $value,
        );

        return $this->copyWithHeaders($headersCopy);
    }

    public function withoutHeader(string $name): static
    {
        $headersCopy = $this->headers->copy();
        $headersCopy->remove($this->normalizeHeaderName($name));

        return $this->copyWithHeaders($headersCopy);
    }

    public function withProtocolVersion(string $version): static
    {
        if ($this->protocol === $version) {
            return $this;
        }

        /**
         * @var static
         */
        return new self(
            $this->response,
            $this->body,
            $this->code,
            $this->headers,
            $version,
            $this->reasonPhrase,
            $this->version + 1,
        );
    }

    public function withStatus(int $code, string $reasonPhrase = ''): static
    {
        if ($code === $this->code && $reasonPhrase === $this->reasonPhrase) {
            return $this;
        }

        /**
         * @var static
         */
        return new self(
            $this->response,
            $this->body,
            $code,
            $this->headers,
            $this->protocol,
            $reasonPhrase,
            $this->version + 1,
        );
    }

    /**
     * @param Map<string,array<string>> $headersCopy
     */
    private function copyWithHeaders(Map $headersCopy): static
    {
        /**
         * @var static
         */
        return new self(
            $this->response,
            $this->body,
            $this->code,
            $headersCopy,
            $this->protocol,
            $this->reasonPhrase,
            $this->version + 1,
        );
    }

    private function normalizeHeaderName(string $name): string
    {
        return strtolower($name);
    }

    private function throwWriteOnly(): never
    {
        throw new LogicException('This response is write-only');
    }
}
