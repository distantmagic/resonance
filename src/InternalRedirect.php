<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class InternalRedirect implements HttpInterceptableInterface
{
    private SwooleContextRequestResponseReader $swooleContextRequestResponseReader;

    /**
     * @param array<string,string> $params
     */
    public function __construct(
        public HttpRouteSymbolInterface $routeSymbol,
        public array $params = [],
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
