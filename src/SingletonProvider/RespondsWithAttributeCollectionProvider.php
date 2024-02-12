<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RespondsWith;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpResponderCollection;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\PHPFileReflectionClassAttribute;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\RespondsWithAttributeCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<HttpResponderCollection>
 */
#[Singleton(provides: RespondsWithAttributeCollection::class)]
final readonly class RespondsWithAttributeCollectionProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): RespondsWithAttributeCollection
    {
        $respondsWithAttributeCollection = new RespondsWithAttributeCollection();

        /**
         * @var PHPFileReflectionClassAttribute<HttpResponderInterface,RespondsWith>
         */
        foreach ($phpProjectFiles->findByAttribute(RespondsWith::class) as $attribute) {
            $respondsWithAttributeCollection->addAttribute($attribute);
        }

        return $respondsWithAttributeCollection;
    }
}
