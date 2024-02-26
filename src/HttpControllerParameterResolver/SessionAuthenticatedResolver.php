<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpControllerParameterResolver;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\ResolvesHttpControllerParameter;
use Distantmagic\Resonance\Attribute\SessionAuthenticated;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\HttpControllerParameter;
use Distantmagic\Resonance\HttpControllerParameterResolution;
use Distantmagic\Resonance\HttpControllerParameterResolutionStatus;
use Distantmagic\Resonance\HttpControllerParameterResolver;
use Distantmagic\Resonance\SessionAuthentication;
use Distantmagic\Resonance\SingletonCollection;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @template-extends HttpControllerParameterResolver<SessionAuthenticated>
 */
#[GrantsFeature(Feature::HttpSession)]
#[ResolvesHttpControllerParameter(SessionAuthenticated::class)]
#[Singleton(collection: SingletonCollection::HttpControllerParameterResolver)]
readonly class SessionAuthenticatedResolver extends HttpControllerParameterResolver
{
    public function __construct(private SessionAuthentication $sessionAuthentication) {}

    public function resolve(
        ServerRequestInterface $request,
        ResponseInterface $response,
        HttpControllerParameter $parameter,
        Attribute $attribute,
    ): HttpControllerParameterResolution {
        $user = $this->sessionAuthentication->getAuthenticatedUser($request)?->user;

        if (!is_null($user)) {
            if ($user instanceof $parameter->className) {
                return new HttpControllerParameterResolution(
                    HttpControllerParameterResolutionStatus::Success,
                    $user,
                );
            }

            throw new LogicException(sprintf(
                'Expected user to be an instance of: %s. Got: %s',
                $parameter->className,
                $user::class,
            ));
        }

        if ($parameter->reflectionParameter->isOptional()) {
            return new HttpControllerParameterResolution(HttpControllerParameterResolutionStatus::Success);
        }

        return new HttpControllerParameterResolution(HttpControllerParameterResolutionStatus::Forbidden);
    }
}
