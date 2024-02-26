<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\PsrMessage;

use Distantmagic\Resonance\ApplicationConfiguration;
use Distantmagic\Resonance\PsrMessage;
use Distantmagic\Resonance\PsrStringStream;
use Distantmagic\Resonance\SwooleConfiguration;
use Distantmagic\Resonance\SwooleServerRequestServer;
use Distantmagic\Resonance\SwooleServerRequestUri;
use InvalidArgumentException;
use LogicException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Swoole\Http\Request;

readonly class SwooleServerRequest extends PsrMessage implements ServerRequestInterface
{
    private PsrStringStream $body;

    /**
     * @var array<array-key,array<string>>
     */
    private array $psrHeaders;

    private SwooleServerRequestServer $server;
    private SwooleServerRequestUri $uri;

    public function __construct(
        ApplicationConfiguration $applicationConfiguration,
        private Request $request,
        SwooleConfiguration $swooleConfiguration,
    ) {
        /**
         * @var array<array-key,array<string>>
         */
        $psrHeaders = [];

        if (is_array($request->header)) {
            /**
             * @var non-empty-string $headerName
             * @var string           $headerValue
             */
            foreach ($request->header as $headerName => $headerValue) {
                $psrHeaders[$headerName] = [$headerValue];
            }
        }

        $requestContents = $request->getContent();

        if (!is_string($requestContents)) {
            throw new InvalidArgumentException('Request content is not a string');
        }

        $this->body = new PsrStringStream($requestContents);
        $this->psrHeaders = $psrHeaders;
        $this->server = new SwooleServerRequestServer($request);
        $this->uri = new SwooleServerRequestUri(
            $applicationConfiguration,
            $this->server,
            $swooleConfiguration,
        );
    }

    public function getAttribute(string $name, $default = null): mixed
    {
        return $default;
    }

    public function getAttributes(): array
    {
        return [];
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function getCookieParams(): array
    {
        if (is_array($this->request->cookie)) {
            return $this->request->cookie;
        }

        return [];
    }

    public function getHeader($name): array
    {
        return $this->psrHeaders[$name] ?? [];
    }

    public function getHeaderLine(string $name): string
    {
        if (!is_array($this->request->header)) {
            return '';
        }

        if (!array_key_exists($name, $this->request->header)) {
            return '';
        }

        if (!is_string($this->request->header[$name])) {
            return '';
        }

        return $this->request->header[$name];
    }

    public function getHeaders(): array
    {
        return $this->psrHeaders;
    }

    public function getMethod(): string
    {
        return $this->server->getServerVariable('request_method');
    }

    public function getParsedBody(): ?array
    {
        /**
         * @var null|array
         */
        return $this->request->post;
    }

    public function getProtocolVersion(): string
    {
        return $this->server->getServerVariable('server_protocol');
    }

    public function getQueryParams(): array
    {
        if (is_array($this->request->get)) {
            return $this->request->get;
        }

        return [];
    }

    public function getRequestTarget(): string
    {
        return $this->server->getServerVariable('request_uri');
    }

    public function getServerParams(): array
    {
        if (is_array($this->request->server)) {
            return $this->request->server;
        }

        return [];
    }

    /**
     * @return array<UploadedFileInterface>
     */
    public function getUploadedFiles(): array
    {
        return [];
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function hasHeader(string $name): bool
    {
        return array_key_exists($name, $this->psrHeaders);
    }

    public function withAddedHeader($name, $value): never
    {
        $this->throwReadOnly();
    }

    public function withAttribute(string $name, mixed $value): never
    {
        $this->throwReadOnly();
    }

    public function withBody(StreamInterface $body): never
    {
        $this->throwReadOnly();
    }

    public function withCookieParams(array $cookies): never
    {
        $this->throwReadOnly();
    }

    public function withHeader($name, $value): never
    {
        $this->throwReadOnly();
    }

    public function withMethod($method): never
    {
        $this->throwReadOnly();
    }

    public function withoutAttribute(string $name): never
    {
        $this->throwReadOnly();
    }

    public function withoutHeader($name): never
    {
        $this->throwReadOnly();
    }

    public function withParsedBody($data): never
    {
        $this->throwReadOnly();
    }

    public function withProtocolVersion($version): never
    {
        $this->throwReadOnly();
    }

    public function withQueryParams(array $query): never
    {
        $this->throwReadOnly();
    }

    public function withRequestTarget($requestTarget): never
    {
        $this->throwReadOnly();
    }

    public function withUploadedFiles(array $uploadedFiles): never
    {
        $this->throwReadOnly();
    }

    public function withUri(UriInterface $uri, $preserveHost = false): never
    {
        $this->throwReadOnly();
    }

    private function throwReadOnly(): never
    {
        throw new LogicException('This request is readonly');
    }
}
