<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder;

use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpResponderInterface;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class Override extends HttpResponder
{
    public static function assertResponder(HttpInterceptableInterface|HttpResponderInterface $responder): HttpResponderInterface
    {
        if (!($responder instanceof HttpResponderInterface)) {
            throw new InvalidArgumentException('The responder must be an instance of '.HttpResponderInterface::class);
        }

        return $responder;
    }

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
