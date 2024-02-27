<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\ContentType;
use Distantmagic\Resonance\HttpResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[Singleton]
final readonly class NotAcceptable extends HttpResponder
{
    public function respond(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response
            ->withStatus(406)
            ->withHeader('content-type', ContentType::TextPlain->value)
            ->withBody($this->createStream('406'))
        ;
    }
}
