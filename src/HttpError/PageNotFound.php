<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpError;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpError;
use Swoole\Http\Request;

#[Singleton]
final readonly class PageNotFound extends HttpError
{
    public function code(): int
    {
        return 404;
    }

    public function message(Request $request): string
    {
        return $this->translatorBridge->trans($request, 'error.page_not_found');
    }
}
