<?php

declare(strict_types=1);

namespace Resonance;

use Ds\Map;
use Nette\Schema\Elements\Type;
use Nette\Schema\Processor;
use Nette\Schema\Schema;
use Swoole\Http\Request;

/**
 * @template TValidatedModel of InputValidatedData
 * @template TValidatedData
 */
abstract readonly class InputValidator
{
    public const REGEXP_SLUG = '^[a-z][-a-z0-9]*$';

    /**
     * @var Map<string,InputValidatorAssertion>
     */
    protected Map $extraAssertions;

    private Processor $processor;
    private Schema $schema;

    /**
     * @param TValidatedData $data
     *
     * @return TValidatedModel
     */
    abstract protected function castValidatedData(mixed $data): InputValidatedData;

    abstract protected function makeSchema(): Schema;

    /**
     * @param array<string,InputValidatorAssertion> $extraAssertions
     */
    public function __construct(array $extraAssertions = [])
    {
        $this->extraAssertions = new Map($extraAssertions);
        $this->processor = new Processor();
        $this->schema = $this->makeSchema();
    }

    /**
     * @return TValidatedModel
     */
    public function validateData(mixed $data): InputValidatedData
    {
        /**
         * Nette schema validation library is trusted to do that (cast anything
         * into the validated data)
         *
         * @var TValidatedData $validatedData
         */
        $validatedData = $this->processor->process($this->schema, $data);

        return $this->castValidatedData($validatedData);
    }

    /**
     * @return TValidatedModel
     */
    public function validateRequest(Request $request): InputValidatedData
    {
        return $this->validateData($request->post);
    }

    /**
     * @psalm-suppress UnsafeInstantiation
     *
     * @todo Until PHP 8.3 introduces the ability to clone readonly properties,
     * this method needs to employ UnsafeInstantiation or some form of
     * reflection.
     *
     * @param array<string,InputValidatorAssertion> $extraAssertions
     *
     * @return InputValidator<TValidatedModel,TValidatedData>
     */
    public function withExtraAssertions(array $extraAssertions): self
    {
        /**
         * @var InputValidator<TValidatedModel,TValidatedData>
         */
        return new static($extraAssertions);
    }

    protected function optionallyUseExtraAssertion(string $name, Type $schema): Type
    {
        if (!$this->extraAssertions->hasKey($name)) {
            return $schema;
        }

        $assertion = $this->extraAssertions->get($name);

        return $schema->assert(
            $assertion->assert(...),
            $assertion->getMessage(),
        );
    }
}
