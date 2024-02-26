<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Event;

use Distantmagic\Resonance\Event;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\LoggableInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class HttpResponseReady extends Event implements LoggableInterface
{
    public function __construct(
        public HttpResponderInterface $responder,
        public ServerRequestInterface $request,
    ) {}

    public function shouldLog(): bool
    {
        return false;
    }
}
