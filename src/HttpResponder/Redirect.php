<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder;

use Distantmagic\Resonance\HttpResponder;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Response;

readonly class Redirect extends HttpResponder
{
    public function __construct(
        private string $location,
        private int $code = 303,
    ) {}

    public function respond(ServerRequestInterface $request, Response $response): null
    {
        $response->status($this->code);
        $response->header('location', $this->location);

        return null;
    }
}
