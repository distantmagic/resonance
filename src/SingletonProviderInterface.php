<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TObject of object
 */
interface SingletonProviderInterface
{
    /**
     * @return TObject
     */
    public function provide(
        SingletonContainer $singletons,
        PHPProjectFiles $phpProjectFiles,
    ): object;
}
