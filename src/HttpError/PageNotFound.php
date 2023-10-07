<?php

declare(strict_types=1);

namespace Resonance\HttpError;

use Resonance\Attribute\Singleton;
use Resonance\HttpError;
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
        return $this->translator->trans($request, 'error.page_not_found');
    }
}
