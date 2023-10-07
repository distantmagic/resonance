<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use HTMLPurifier;
use HTMLPurifier_Config;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<HTMLPurifier>
 */
#[Singleton(provides: HTMLPurifier::class)]
final readonly class HTMLPurifierProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): HTMLPurifier
    {
        $purifierConfig = HTMLPurifier_Config::createDefault();

        return new HTMLPurifier($purifierConfig);
    }
}
