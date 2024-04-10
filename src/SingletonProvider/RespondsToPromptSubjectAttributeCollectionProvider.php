<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RespondsToPromptSubject;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpResponderCollection;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\RespondsToPromptSubjectAttributeCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<HttpResponderCollection>
 */
#[Singleton(provides: RespondsToPromptSubjectAttributeCollection::class)]
final readonly class RespondsToPromptSubjectAttributeCollectionProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): RespondsToPromptSubjectAttributeCollection
    {
        $httpResponderAttributeCollection = new RespondsToPromptSubjectAttributeCollection();

        foreach ($phpProjectFiles->findClassByAttribute(RespondsToPromptSubject::class) as $attribute) {
            $httpResponderAttributeCollection->attributes->add($attribute->attribute);
        }

        return $httpResponderAttributeCollection;
    }
}
