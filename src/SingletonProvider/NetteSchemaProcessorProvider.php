<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Nette\Schema\Processor;

/**
 * @template-extends SingletonProvider<Processor>
 */
#[Singleton(provides: Processor::class)]
final readonly class NetteSchemaProcessorProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): Processor
    {
        return new Processor();
    }
}
