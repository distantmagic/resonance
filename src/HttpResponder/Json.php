<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder;

use Distantmagic\Resonance\ContentType;
use Distantmagic\Resonance\HttpResponder;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Response;

readonly class Json extends HttpResponder
{
    public function __construct(private string $json) {}

    public function respond(ServerRequestInterface $request, Response $response): null
    {
        $response->header('content-type', ContentType::ApplicationJson->value);
        $response->end($this->json);

        return null;
    }
}
