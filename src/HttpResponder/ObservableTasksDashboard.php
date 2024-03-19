<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[Singleton]
readonly class ObservableTasksDashboard extends HttpResponder
{
    public function respond(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response->withBody($this->createStream('{"tasks": []}'));
    }
}
