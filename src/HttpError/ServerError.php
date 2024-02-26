<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpError;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpError;
use Psr\Http\Message\ServerRequestInterface;

#[Singleton]
final readonly class ServerError extends HttpError
{
    public function code(): int
    {
        return 500;
    }

    public function message(ServerRequestInterface $request): string
    {
        return $this->translatorBridge->trans($request, 'error.server_error');
    }
}
