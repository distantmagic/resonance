<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use LogicException;
use Swoole\Http\Request;
use Swoole\Http\Response;

#[Singleton]
readonly class OAuth2AuthorizationRequestSessionStore
{
    public const SESSION_KEY = 'oauth2.authorization_request';

    public function __construct(private SessionManager $sessionManager) {}

    public function clear(Request $request, Response $response): void
    {
        $this
            ->sessionManager
            ->start($request, $response)
            ->data
            ->remove(self::SESSION_KEY, null)
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
            ->put(self::SESSION_KEY, $authorizationRequest)
        ;
    }

    private function doGetFromSession(Request $request, Response $response): ?AuthorizationRequest
    {
        /**
         * @var mixed explicitly mixed for typechecks
         */
        $authorizationRequest = $this
            ->sessionManager
            ->start($request, $response)
            ->data
            ->get(self::SESSION_KEY, null)
        ;

        if ($authorizationRequest instanceof AuthorizationRequest) {
            return $authorizationRequest;
        }

        return null;
    }
}
