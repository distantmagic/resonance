<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\ConfigurationFile;
use Distantmagic\Resonance\ConstraintPath;
use Distantmagic\Resonance\ConstraintSourceInterface;
use Distantmagic\Resonance\ConstraintValidationException;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\RegisterableInterface;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template TObject of object
 * @template TSchema
 *
 * @template-extends SingletonProvider<TObject>
 */
abstract readonly class ConfigurationProvider extends SingletonProvider implements ConstraintSourceInterface, RegisterableInterface
{
    abstract protected function getConfigurationKey(): string;

    /**
     * @param TSchema $validatedData
     *
     * @return TObject
     */
    abstract protected function provideConfiguration($validatedData): object;

    public function __construct(
        private ConfigurationFile $configurationFile,
    ) {}

    /**
     * @return TObject
     */
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): object
    {
        $configurationKey = $this->getConfigurationKey();

        /**
         * @var mixed $data explicitly mixed for typechecks
         */
        $data = $this->configurationFile->config[$configurationKey];

        $path = new ConstraintPath([$configurationKey]);
        $constraintResult = $this->getConstraint()->validate($data, $path);

        if ($constraintResult->status->isValid()) {
            /**
             * @var TSchema $constraintResult->castedData
             */
            return $this->provideConfiguration($constraintResult->castedData);
        }

        throw new ConstraintValidationException($constraintResult);
    }

    public function shouldRegister(): bool
    {
        return true;
    }
}
