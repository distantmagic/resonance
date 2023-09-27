<?php

declare(strict_types=1);

namespace Resonance;

/**
 * @template TObject of object
 */
interface SingletonProviderInterface
{
    /**
     * @return TObject
     */
    public function provide(SingletonContainer $singletons): object;

    /**
     * Let the singleton container builder know if you want this singleton to
     * be registered.
     *
     * This is a good place check global state like environmental variables,
     * configuration and make a decision upon those.
     */
    public function shouldRegister(): bool;
}
