<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpResponderCollection;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\PHPFileReflectionClassAttribute;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\RespondsToHttpAttributeCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<HttpResponderCollection>
 */
#[Singleton(provides: RespondsToHttpAttributeCollection::class)]
final readonly class RespondsToHttpAttributeCollectionProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): RespondsToHttpAttributeCollection
    {
        $httpResponderAttributeCollection = new RespondsToHttpAttributeCollection();

        /**
         * @var PHPFileReflectionClassAttribute<HttpResponderInterface,RespondsToHttp>
         */
        foreach ($phpProjectFiles->findByAttribute(RespondsToHttp::class) as $attribute) {
            $httpResponderAttributeCollection->attributes->put(
                $attribute->reflectionClass->getName(),
                $attribute
            );
        }

        return $httpResponderAttributeCollection;
    }
}
