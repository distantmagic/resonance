<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder;

use Distantmagic\Resonance\ContentType;
use Distantmagic\Resonance\HttpResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class Json extends HttpResponder
{
    public function __construct(private string $json) {}

    public function respond(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response
            ->withStatus(200)
            ->withHeader('content-type', ContentType::ApplicationJson->value)
            ->withBody($this->createStream($this->json))
        ;
    }
}
