<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\ContentType;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpResponderInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Response;

#[Singleton]
final readonly class NotAcceptable extends HttpResponder
{
    public function respond(ServerRequestInterface $request, Response $response): ?HttpResponderInterface
    {
        $response->status(406);
        $response->header('content-type', ContentType::TextPlain->value);
        $response->end('406');

        return null;
    }
}
