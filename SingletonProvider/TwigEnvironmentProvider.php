<?php

declare(strict_types=1);

namespace Resonance\SingletonProvider;

use Resonance\Attribute\Singleton;
use Resonance\PHPProjectFiles;
use Resonance\SingletonContainer;
use Resonance\SingletonProvider;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * @template-extends SingletonProvider<Environment>
 */
#[Singleton(provides: Environment::class)]
final readonly class TwigEnvironmentProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): Environment
    {
        $loader = new FilesystemLoader(DM_APP_ROOT.'/views');

        return new Environment($loader, [
            'cache' => DM_ROOT.'/cache/twig',
        ]);
    }
}
