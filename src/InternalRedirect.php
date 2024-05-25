<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class InternalRedirect implements HttpInterceptableInterface
{
    /**
     * @param array<string,string> $params
     */
    public function __construct(
        private ServerRequestInterface $request,
        private ResponseInterface $response,
        public HttpRouteSymbolInterface $routeSymbol,
        public array $params = [],
    ) {}

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getServerRequest(): ServerRequestInterface
    {
        return $this->request;
    }
}
