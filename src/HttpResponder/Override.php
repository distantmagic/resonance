<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder;

use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpResponderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class Override extends HttpResponder
{
    public function __construct(
        private HttpResponderInterface $responder,
        private ServerRequestInterface $request,
        private ResponseInterface $response,
    ) {}

    public function respond(ServerRequestInterface $request, ResponseInterface $response): HttpInterceptableInterface|HttpResponderInterface|ResponseInterface
    {
        return $this->responder->respond($this->request, $this->response);
    }
}
