<?php

declare(strict_types=1);

namespace Resonance\SingletonProvider;

use Resonance\Attribute\Singleton;
use Resonance\EsbuildMeta;
use Resonance\EsbuildMetaBuilder;
use Resonance\PHPProjectFiles;
use Resonance\SingletonContainer;
use Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<EsbuildMeta>
 */
#[Singleton(provides: EsbuildMeta::class)]
final readonly class EsbuildMetaProvider extends SingletonProvider
{
    public function __construct() {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): EsbuildMeta
    {
        $builder = new EsbuildMetaBuilder();

        return $builder->build(DM_ROOT.'/esbuild-meta-app.json');
    }
}
