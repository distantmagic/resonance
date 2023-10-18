<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Nette\PhpGenerator\Printer;

/**
 * @template-extends SingletonProvider<Printer>
 */
#[Singleton(provides: Printer::class)]
final readonly class NettePrinterProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): Printer
    {
        $printer = new Printer();
        $printer->indentation = '    ';

        return $printer;
    }
}
