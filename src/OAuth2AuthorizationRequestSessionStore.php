<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\Singleton;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use LogicException;
use Psr\Http\Message\ServerRequestInterface;

#[GrantsFeature(Feature::OAuth2)]
#[Singleton]
readonly class OAuth2AuthorizationRequestSessionStore
{
    public function __construct(
        private OAuth2Configuration $oAuth2Configuration,
        private SessionManager $sessionManager,
    ) {}

    public function clear(ServerRequestInterface $request): void
    {
        $this->sessionManager->start($request)->data->remove(
            $this->oAuth2Configuration->sessionKeyAuthorizationRequest,
            null,
        );
    }

    public function get(ServerRequestInterface $request): AuthorizationRequest
    {
        $authorizationRequest = $this->doGetFromSession($request);

        if (!$authorizationRequest) {
            throw new LogicException('Authorization request is not in session.');
        }

        return $authorizationRequest;
    }

    public function has(ServerRequestInterface $request): bool
    {
        $authorizationRequest = $this->doGetFromSession($request);

        return $authorizationRequest instanceof AuthorizationRequest;
    }

    public function store(
        ServerRequestInterface $request,
        AuthorizationRequest $authorizationRequest,
    ): void {
        $this->sessionManager->start($request)->data->put(
            $this->oAuth2Configuration->sessionKeyAuthorizationRequest,
            $authorizationRequest,
        );
    }

    private function doGetFromSession(ServerRequestInterface $request): ?AuthorizationRequest
    {
        $session = $this->sessionManager->start($request);

        /**
         * @var mixed explicitly mixed for typechecks
         */
        $authorizationRequest = $session->data->get(
            $this->oAuth2Configuration->sessionKeyAuthorizationRequest,
            null,
        );

        if ($authorizationRequest instanceof AuthorizationRequest) {
            return $authorizationRequest;
        }

        return null;
    }
}
