<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\ConfigurationFile;
use Distantmagic\Resonance\JsonSchemaSourceInterface;
use Distantmagic\Resonance\JsonSchemaValidationException;
use Distantmagic\Resonance\JsonSchemaValidationResult;
use Distantmagic\Resonance\JsonSchemaValidator;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template TObject of object
 * @template TSchema
 *
 * @template-extends SingletonProvider<TObject>
 */
abstract readonly class ConfigurationProvider extends SingletonProvider implements JsonSchemaSourceInterface
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
        private JsonSchemaValidator $jsonSchemaValidator,
    ) {}

    /**
     * @return TObject
     */
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): object
    {
        /**
         * @var mixed $data explicitly mixed for typechecks
         */
        $data = $this->configurationFile->config->get($this->getConfigurationKey());

        /**
         * @var JsonSchemaValidationResult<TSchema>
         */
        $jsonSchemaValidationResult = $this->jsonSchemaValidator->validate($this, $data);

        $errors = $jsonSchemaValidationResult->errors;

        if (empty($errors)) {
            return $this->provideConfiguration($jsonSchemaValidationResult->data);
        }

        throw new JsonSchemaValidationException($errors);
    }

    public function shouldRegister(): bool
    {
        return $this->configurationFile->config->has($this->getConfigurationKey());
    }
}
