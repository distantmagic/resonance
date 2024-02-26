<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\ServerRequestInterface;

abstract readonly class HttpError
{
    abstract public function code(): int;

    abstract public function message(ServerRequestInterface $request): string;

    public function __construct(protected TranslatorBridge $translatorBridge) {}
}
