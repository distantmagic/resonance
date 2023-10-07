<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Nette\Schema\Processor;
use Nette\Schema\Schema;
use Nette\Schema\ValidationException;
use Swoole\Http\Request;

/**
 * @template TValidatedModel of InputValidatedData
 * @template TValidatedData
 */
abstract readonly class InputValidator
{
    public const REGEXP_SLUG = '^[a-z][-a-z0-9]{3,}$';

    private Processor $processor;
    private Schema $schema;

    /**
     * @param TValidatedData $data
     *
     * @return TValidatedModel
     */
    abstract protected function castValidatedData(mixed $data): InputValidatedData;

    abstract protected function makeSchema(): Schema;

    public function __construct()
    {
        $this->processor = new Processor();
        $this->schema = $this->makeSchema();
    }

    /**
     * @return InputValidationResult<TValidatedModel>
     */
    public function validateData(mixed $data): InputValidationResult
    {
        try {
            /**
             * Nette schema validation library is trusted to do that (cast anything
             * into the validated data)
             *
             * @var TValidatedData $validatedData
             */
            $validatedData = $this->processor->process($this->schema, $data);
        } catch (ValidationException $validationException) {
            /**
             * @var InputValidationResult<TValidatedModel>
             */
            $validationResult = new InputValidationResult();

            foreach ($validationException->getMessageObjects() as $message) {
                $validationResult->errors->put(
                    implode('.', $message->path),
                    $message->toString(),
                );
            }

            return $validationResult;
        }

        return new InputValidationResult(
            inputValidatedData: $this->castValidatedData($validatedData),
        );
    }

    /**
     * @return InputValidationResult<TValidatedModel>
     */
    public function validateRequest(Request $request): InputValidationResult
    {
        return $this->validateData($request->post);
    }
}
