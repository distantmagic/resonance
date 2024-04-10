<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stringable;

final readonly class JsonTemplate implements HttpInterceptableInterface
{
    private SwooleContextRequestResponseReader $swooleContextRequestResponseReader;

    public function __construct(
        public array|JsonSerializable|string|Stringable $data,
        ?ServerRequestInterface $request = null,
        ?ResponseInterface $response = null,
    ) {
        $this->swooleContextRequestResponseReader = new SwooleContextRequestResponseReader(
            request: $request,
            response: $response,
        );
    }

    public function getResponse(): ResponseInterface
    {
        return $this->swooleContextRequestResponseReader->getResponse();
    }

    public function getServerRequest(): ServerRequestInterface
    {
        return $this->swooleContextRequestResponseReader->getServerRequest();
    }
}
