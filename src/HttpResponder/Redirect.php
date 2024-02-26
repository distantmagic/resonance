<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder;

use Distantmagic\Resonance\HttpResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class Redirect extends HttpResponder
{
    public function __construct(
        private string $location,
        private int $code = 303,
    ) {}

    public function respond(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response
            ->withStatus($this->code)
            ->withHeader('location', $this->location)
        ;
    }
}
