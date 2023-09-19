<?php

declare(strict_types=1);

namespace Resonance;

use Swoole\Http\Request;
use Swoole\Http\Response;

interface HttpResponderInterface
{
    public function respond(Request $request, Response $response): void;
}
