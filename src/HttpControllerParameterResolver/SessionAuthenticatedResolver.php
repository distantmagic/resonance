<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpControllerParameterResolver;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\ResolvesHttpControllerParameter;
use Distantmagic\Resonance\Attribute\SessionAuthenticated;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpControllerParameter;
use Distantmagic\Resonance\HttpControllerParameterResolution;
use Distantmagic\Resonance\HttpControllerParameterResolutionStatus;
use Distantmagic\Resonance\HttpControllerParameterResolver;
use Distantmagic\Resonance\SessionAuthentication;
use Distantmagic\Resonance\SingletonCollection;
use LogicException;
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * @template-extends HttpControllerParameterResolver<SessionAuthenticated>
 */
#[ResolvesHttpControllerParameter(SessionAuthenticated::class)]
#[Singleton(collection: SingletonCollection::HttpControllerParameterResolver)]
readonly class SessionAuthenticatedResolver extends HttpControllerParameterResolver
{
    public function __construct(private SessionAuthentication $sessionAuthentication) {}

    public function resolve(
        Request $request,
        Response $response,
        HttpControllerParameter $parameter,
        Attribute $attribute,
    ): HttpControllerParameterResolution {
        $user = $this->sessionAuthentication->authenticatedUser($request);

        if (!is_null($user)) {
            if ($user instanceof $parameter->className) {
                return new HttpControllerParameterResolution(
                    HttpControllerParameterResolutionStatus::Success,
                    $user,
                );
            }

            throw new LogicException('Expected user to be an instance of: '.$parameter->className);
        }

        if ($parameter->reflectionParameter->isOptional()) {
            return new HttpControllerParameterResolution(HttpControllerParameterResolutionStatus::Success);
        }

        return new HttpControllerParameterResolution(HttpControllerParameterResolutionStatus::Forbidden);
    }
}
