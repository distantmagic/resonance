<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class InternalRedirect implements HttpInterceptableInterface
{
    private ServerRequestInterface $request;
    private ResponseInterface $response;

    /**
     * @param array<string,string> $params
     */
    public function __construct(
        public HttpRouteSymbolInterface $routeSymbol,
        public array $params = [],
        ?ResponseInterface $response = null,
    ) {
        $context = SwooleCoroutineHelper::mustGetContext();

        $this->request = $context['psr_http_request'];
        $this->response = $response ?? $context['psr_http_response'];
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getServerRequest(): ServerRequestInterface
    {
        return $this->request;
    }
}
