<?php

declare(strict_types=1);

namespace Resonance;

use Resonance\Attribute\Singleton;
use Swoole\Http\Request;
use Swoole\Http\Response;

#[Singleton]
readonly class TwigFunctionCSRFToken
{
    public function __construct(private CSRFManager $csrfManager) {}

    public function __invoke(Request $request, Response $response): string
    {
        return $this->csrfManager->prepareSessionToken($request, $response);
    }
}
