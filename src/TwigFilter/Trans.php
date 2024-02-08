<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\TwigFilter;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\TwigFilter as TwigFilterAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\TranslatorBridge;
use Distantmagic\Resonance\TwigFilter;
use Swoole\Http\Request;

#[Singleton(collection: SingletonCollection::TwigFilter)]
#[TwigFilterAttribute]
readonly class Trans extends TwigFilter
{
    public function __construct(private TranslatorBridge $translatorBridge) {}

    /**
     * @param array<string, string> $parameters
     */
    public function __invoke(string $message, Request $request, array $parameters = []): string
    {
        return $this->translatorBridge->trans($request, $message, $parameters);
    }

    public function getName(): string
    {
        return 'trans';
    }
}
