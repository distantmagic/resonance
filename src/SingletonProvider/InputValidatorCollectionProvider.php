<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\InputValidator;
use Distantmagic\Resonance\InputValidatorCollection;
use Distantmagic\Resonance\InputValidatorController;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<InputValidatorCollection>
 */
#[RequiresSingletonCollection(SingletonCollection::InputValidator)]
#[Singleton(provides: InputValidatorCollection::class)]
final readonly class InputValidatorCollectionProvider extends SingletonProvider
{
    public function __construct(
        private InputValidatorController $inputValidatorController,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): InputValidatorCollection
    {
        $inputValidatorCollection = new InputValidatorCollection();

        foreach ($singletons->values() as $singleton) {
            if ($singleton instanceof InputValidator) {
                $inputValidatorCollection->inputValidators->put(
                    $singleton::class,
                    $singleton,
                );
                $this
                    ->inputValidatorController
                    ->cachedConstraints
                    ->put($singleton, $singleton->getConstraint())
                ;
            }
        }

        return $inputValidatorCollection;
    }
}
