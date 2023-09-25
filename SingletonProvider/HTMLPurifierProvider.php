<?php

declare(strict_types=1);

namespace Resonance\SingletonProvider;

use HTMLPurifier;
use HTMLPurifier_Config;
use Resonance\Attribute\Singleton;
use Resonance\SingletonContainer;
use Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<HTMLPurifier>
 */
#[Singleton(provides: HTMLPurifier::class)]
final readonly class HTMLPurifierProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons): HTMLPurifier
    {
        $purifierConfig = HTMLPurifier_Config::createDefault();

        return new HTMLPurifier($purifierConfig);
    }
}
