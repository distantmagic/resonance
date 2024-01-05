<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpResponderAttributeCollection;
use Distantmagic\Resonance\HttpResponderCollection;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<HttpResponderCollection>
 */
#[Singleton(provides: HttpResponderAttributeCollection::class)]
final readonly class HttpResponderAttributeCollectionProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): HttpResponderAttributeCollection
    {
        $httpResponderAttributeCollection = new HttpResponderAttributeCollection();

        foreach ($phpProjectFiles->findByAttribute(RespondsToHttp::class) as $responderAttribute) {
            $httpResponderAttributeCollection->httpResponderAttributes->add(
                $responderAttribute->attribute
            );
        }

        return $httpResponderAttributeCollection;
    }
}
