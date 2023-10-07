<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpError;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpError;
use Swoole\Http\Request;

#[Singleton]
final readonly class Forbidden extends HttpError
{
    public function code(): int
    {
        return 403;
    }

    public function message(Request $request): string
    {
        return $this->translator->trans($request, 'error.forbidden');
    }
}
