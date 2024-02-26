<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpControllerParameterResolver;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\ResolvesHttpControllerParameter;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\ValidatedRequest;
use Distantmagic\Resonance\HttpControllerParameter;
use Distantmagic\Resonance\HttpControllerParameterResolution;
use Distantmagic\Resonance\HttpControllerParameterResolutionStatus;
use Distantmagic\Resonance\HttpControllerParameterResolver;
use Distantmagic\Resonance\InputValidatorCollection;
use Distantmagic\Resonance\InputValidatorController;
use Distantmagic\Resonance\SingletonCollection;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @template-extends HttpControllerParameterResolver<ValidatedRequest>
 */
#[ResolvesHttpControllerParameter(ValidatedRequest::class)]
#[Singleton(collection: SingletonCollection::HttpControllerParameterResolver)]
readonly class ValidatedRequestResolver extends HttpControllerParameterResolver
{
    public function __construct(
        private InputValidatorCollection $inputValidatorCollection,
        private InputValidatorController $inputValidatorController,
    ) {}

    public function resolve(
        ServerRequestInterface $request,
        ResponseInterface $response,
        HttpControllerParameter $parameter,
        Attribute $attribute,
    ): HttpControllerParameterResolution {
        $validatorClassName = $attribute->validator;

        if (!$this->inputValidatorCollection->inputValidators->hasKey($validatorClassName)) {
            throw new LogicException('Validator is not registered: '.$validatorClassName);
        }

        $validator = $this->inputValidatorCollection->inputValidators->get($validatorClassName);
        $validationResult = $this
            ->inputValidatorController
            ->validateData($validator, $request->getParsedBody() ?? [])
        ;

        if ($validationResult->inputValidatedData) {
            if ($validationResult->inputValidatedData instanceof $parameter->className) {
                return new HttpControllerParameterResolution(
                    HttpControllerParameterResolutionStatus::Success,
                    $validationResult->inputValidatedData,
                );
            }

            throw new LogicException('Expected input validated data to be: '.$parameter->className);
        }

        return new HttpControllerParameterResolution(
            HttpControllerParameterResolutionStatus::ValidationErrors,
            $validationResult->constraintResult->getErrors(),
        );
    }
}
