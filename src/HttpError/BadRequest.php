<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpError;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpError;
use Swoole\Http\Request;

#[Singleton]
final readonly class BadRequest extends HttpError
{
    public function code(): int
    {
        return 400;
    }

    public function message(Request $request): string
    {
        return $this->translator->trans($request, 'error.bad_request');
    }
}
