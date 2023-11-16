<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Swoole\Http\Request;

#[Singleton]
readonly class TwigFilterTrans
{
    public function __construct(private TranslatorBridge $translatorBridge) {}

    /**
     * @param array<string, string> $parameters
     */
    public function __invoke(string $message, Request $request, array $parameters = []): string
    {
        return $this->translatorBridge->trans($request, $message, $parameters);
    }
}
