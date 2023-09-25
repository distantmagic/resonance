<?php

declare(strict_types=1);

namespace Resonance;

/**
 * @template TObject of object
 *
 * @template-extends SingletonProviderInterface<TObject>
 */
interface LazySingletonProviderInterface extends SingletonProviderInterface {}
