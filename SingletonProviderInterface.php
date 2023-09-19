<?php

declare(strict_types=1);

namespace Resonance;

use Symfony\Component\Console\Output\ConsoleOutputInterface;

/**
 * @template TObject of object
 */
interface SingletonProviderInterface
{
    /**
     * @return TObject
     */
    public function provide(SingletonContainer $singletons, ?ConsoleOutputInterface $output = null): object;
}
