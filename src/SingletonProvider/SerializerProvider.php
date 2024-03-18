<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\Serializer\Igbinary;
use Distantmagic\Resonance\Serializer\Vanilla;
use Distantmagic\Resonance\SerializerInterface;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<SerializerInterface>
 */
#[Singleton(provides: SerializerInterface::class)]
final readonly class SerializerProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): SerializerInterface
    {
        if (extension_loaded('igbinary')) {
            return new Igbinary();
        }

        return new Vanilla();
    }
}
