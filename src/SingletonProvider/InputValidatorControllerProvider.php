<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\InputValidatorCollection;
use Distantmagic\Resonance\InputValidatorController;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Nette\PhpGenerator\Printer;

/**
 * @template-extends SingletonProvider<Printer>
 */
#[Singleton(provides: InputValidatorController::class)]
final readonly class InputValidatorControllerProvider extends SingletonProvider
{
    public function __construct(
        private InputValidatorCollection $inputValidatorCollection,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): InputValidatorController
    {
        $inputValidatorController = new InputValidatorController();

        foreach ($this->inputValidatorCollection->inputValidators as $inputValidator) {
            $inputValidatorController
                ->cachedConstraints
                ->put($inputValidator, $inputValidator->getConstraint())
            ;
        }

        return $inputValidatorController;
    }
}
