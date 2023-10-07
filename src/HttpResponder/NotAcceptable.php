<?php

declare(strict_types=1);

namespace Resonance\HttpResponder;

use Resonance\Attribute\Singleton;
use Resonance\ContentType;
use Resonance\HttpResponder;
use Resonance\HttpResponderInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;

#[Singleton]
final readonly class NotAcceptable extends HttpResponder
{
    public function respond(Request $request, Response $response): ?HttpResponderInterface
    {
        $response->status(406);
        $response->header('content-type', ContentType::TextPlain->value);
        $response->end('406');

        return null;
    }
}
