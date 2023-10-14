<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Swoole\Http\Request;

#[Singleton]
readonly class TwigFilterTrans
{
    public function __construct(private TranslatorBridge $translatorBridge) {}

    public function __invoke(string $message, Request $request): string
    {
        return $this->translatorBridge->trans($request, $message);
    }
}
