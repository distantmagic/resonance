<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder;

use Distantmagic\Resonance\HttpResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Response;

readonly class PsrResponder extends HttpResponder
{
    public function __construct(public ResponseInterface $psrResponse) {}

    public function respond(ServerRequestInterface $request, Response $response): null
    {
        $response->status(
            $this->psrResponse->getStatusCode(),
            $this->psrResponse->getReasonPhrase(),
        );

        foreach ($this->psrResponse->getHeaders() as $name => $values) {
            $response->header((string) $name, $values);
        }

        $response->end((string) $this->psrResponse->getBody());

        return null;
    }
}
