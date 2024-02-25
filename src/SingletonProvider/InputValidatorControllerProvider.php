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
use Psr\Log\LoggerInterface;

/**
 * @template-extends SingletonProvider<Printer>
 */
#[Singleton(provides: InputValidatorController::class)]
final readonly class InputValidatorControllerProvider extends SingletonProvider
{
    public function __construct(
        private InputValidatorCollection $inputValidatorCollection,
        private LoggerInterface $logger,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): InputValidatorController
    {
        $inputValidatorController = new InputValidatorController($this->logger);

        foreach ($this->inputValidatorCollection->inputValidators as $inputValidator) {
            $inputValidatorController
                ->cachedConstraints
                ->put($inputValidator, $inputValidator->getConstraint())
            ;
        }

        return $inputValidatorController;
    }
}
