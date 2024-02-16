<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\RespondsToPromptSubject;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\PromptSubjectResponderCollection;
use Distantmagic\Resonance\PromptSubjectResponderInterface;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<PromptSubjectResponderCollection>
 */
#[RequiresSingletonCollection(SingletonCollection::PromptSubjectResponder)]
#[Singleton(provides: PromptSubjectResponderCollection::class)]
final readonly class PromptSubjectResponderCollectionProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): PromptSubjectResponderCollection
    {
        $promptableSubjectCollection = new PromptSubjectResponderCollection();

        foreach ($this->collectPromptSubjectResponders($singletons) as $promptableSubjectAttribute) {
            $promptableSubjectCollection->addPromptSubjectResponder(
                $promptableSubjectAttribute->attribute,
                $promptableSubjectAttribute->singleton,
            );
        }

        return $promptableSubjectCollection;
    }

    /**
     * @return iterable<SingletonAttribute<PromptSubjectResponderInterface,RespondsToPromptSubject>>
     */
    private function collectPromptSubjectResponders(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            PromptSubjectResponderInterface::class,
            RespondsToPromptSubject::class,
        );
    }
}
