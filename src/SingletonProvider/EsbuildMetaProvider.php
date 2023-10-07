<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\EsbuildMeta;
use Distantmagic\Resonance\EsbuildMetaBuilder;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

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
