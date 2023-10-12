<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * @template-extends SingletonProvider<ConsoleOutput>
 */
#[Singleton(provides: ConsoleOutput::class)]
final readonly class ConsoleOutputProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): ConsoleOutput
    {
        return new ConsoleOutput();
    }
}
