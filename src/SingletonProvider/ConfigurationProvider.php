<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\ConfigurationFile;
use Distantmagic\Resonance\JsonSchema;
use Distantmagic\Resonance\JsonSchemaValidationResult;
use Distantmagic\Resonance\JsonSchemaValidator;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use LogicException;

/**
 * @template TObject of object
 * @template TSchema
 *
 * @template-extends SingletonProvider<TObject>
 */
abstract readonly class ConfigurationProvider extends SingletonProvider
{
    private JsonSchema $jsonSchema;

    abstract protected function getConfigurationKey(): string;

    abstract protected function makeSchema(): JsonSchema;

    /**
     * @param TSchema $validatedData
     *
     * @return TObject
     */
    abstract protected function provideConfiguration($validatedData): object;

    public function __construct(
        private ConfigurationFile $configurationFile,
        private JsonSchemaValidator $jsonSchemaValidator,
    ) {
        $this->jsonSchema = $this->makeSchema();
    }

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
        $jsonSchemaValidationResult = $this->jsonSchemaValidator->validate($this->jsonSchema, $data);

        $errors = $jsonSchemaValidationResult->errors;

        if (empty($errors)) {
            return $this->provideConfiguration($jsonSchemaValidationResult->data);
        }

        $messages = [];

        foreach ($errors as $propertyName => $propertyErrors) {
            foreach ($propertyErrors as $propertyError) {
                $messages[] = sprintf('"%s": "%s"', $propertyName, $propertyError);
            }
        }

        throw new LogicException(sprintf(
            "Encountered validation errors:\n-> %s",
            implode("\n-> ", $messages)
        ));
    }

    public function shouldRegister(): bool
    {
        return $this->configurationFile->config->has($this->getConfigurationKey());
    }
}
