<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\PsrMessage;

use Amp\Http\Server\FormParser\Form;
use Amp\Http\Server\Request;
use Distantmagic\Resonance\PsrMessage;
use Distantmagic\Resonance\PsrStringStream;
use LogicException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

readonly class AmpServerRequest extends PsrMessage implements ServerRequestInterface
{
    private PsrStringStream $body;
    private array $psrCookieParams;
    private array $serverParams;

    public function __construct(private Request $request)
    {
        $cookies = $request->getCookies();
        $psrCookieParams = [];
        $requestContents = $request->getBody()->buffer();

        foreach ($cookies as $cookie) {
            $psrCookieParams[$cookie->getName()] = $cookie->getValue();
        }

        $this->body = new PsrStringStream($requestContents);
        $this->psrCookieParams = $psrCookieParams;
        $this->serverParams = $_SERVER;
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
        return $this->psrCookieParams;
    }

    public function getHeader($name): array
    {
        return $this->request->getHeaderArray($name);
    }

    public function getHeaderLine(string $name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    public function getHeaders(): array
    {
        return $this->request->getHeaders();
    }

    public function getMethod(): string
    {
        return $this->request->getMethod();
    }

    public function getParsedBody(): ?array
    {
        return Form::fromRequest($this->request)->getValues();
    }

    public function getProtocolVersion(): string
    {
        if (!array_key_exists('SERVER_PROTOCOL', $this->serverParams)) {
            return '';
        }

        /**
         * @var mixed explicitly mixed for typechecks
         */
        $serverProtocol = $this->serverParams['SERVER_PROTOCOL'];

        if (is_string($serverProtocol)) {
            return $serverProtocol;
        }

        return '';
    }

    public function getQueryParams(): array
    {
        return $this->request->getQueryParameters();
    }

    public function getRequestTarget(): string
    {
        $uri = $this->getUri();
        $path = $uri->getPath();

        $target = '';

        if ('' === $path) {
            $target = '/';
        }

        $query = $uri->getQuery();

        if ('' !== $query) {
            $target .= '?'.$query;
        }

        return $target;
    }

    public function getServerParams(): array
    {
        return $this->serverParams;
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
        return $this->request->getUri();
    }

    public function hasHeader(string $name): bool
    {
        return $this->request->hasHeader($name);
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
