<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use LogicException;
use Swoole\Http\Request;
use Swoole\Http\Response;

#[Singleton(grantsFeature: Feature::OAuth2)]
readonly class OAuth2AuthorizationRequestSessionStore
{
    public function __construct(
        private OAuth2Configuration $oAuth2Configuration,
        private SessionManager $sessionManager,
    ) {}

    public function clear(Request $request, Response $response): void
    {
        $this
            ->sessionManager
            ->start($request, $response)
            ->data
            ->remove(
                $this->oAuth2Configuration->sessionKeyAuthorizationRequest,
                null,
            )
        ;
    }

    public function get(Request $request, Response $response): AuthorizationRequest
    {
        $authorizationRequest = $this->doGetFromSession($request, $response);

        if (!$authorizationRequest) {
            throw new LogicException('Authorization request is not in session.');
        }

        return $authorizationRequest;
    }

    public function has(Request $request, Response $response): bool
    {
        $authorizationRequest = $this->doGetFromSession($request, $response);

        return $authorizationRequest instanceof AuthorizationRequest;
    }

    public function store(
        Request $request,
        Response $response,
        AuthorizationRequest $authorizationRequest,
    ): void {
        $this
            ->sessionManager
            ->start($request, $response)
            ->data
            ->put(
                $this->oAuth2Configuration->sessionKeyAuthorizationRequest,
                $authorizationRequest,
            )
        ;
    }

    private function doGetFromSession(Request $request, Response $response): ?AuthorizationRequest
    {
        $session = $this->sessionManager->start($request, $response);

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
