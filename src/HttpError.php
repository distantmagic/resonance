<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Swoole\Http\Request;

abstract readonly class HttpError
{
    abstract public function code(): int;

    abstract public function message(Request $request): string;

    public function __construct(protected Translator $translator) {}
}
