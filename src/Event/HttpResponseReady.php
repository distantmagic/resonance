<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Event;

use Distantmagic\Resonance\Event;
use Distantmagic\Resonance\HttpResponderInterface;
use Swoole\Http\Request;

final readonly class HttpResponseReady extends Event
{
    public function __construct(
        public HttpResponderInterface $responder,
        public Request $request,
    ) {}
}
