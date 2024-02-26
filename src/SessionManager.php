<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\Singleton;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Response;
use WeakMap;

#[GrantsFeature(Feature::HttpSession)]
#[Singleton]
final readonly class SessionManager
{
    /**
     * @var WeakMap<ServerRequestInterface, Session>
     */
    private WeakMap $sessions;

    public function __construct(
        private RedisConfiguration $redisConfiguration,
        private RedisConnectionPoolRepository $redisConnectionPoolRepository,
        private SessionConfiguration $sessionConfiguration,
    ) {
        /**
         * @var WeakMap<ServerRequestInterface, Session>
         */
        $this->sessions = new WeakMap();
    }

    public function persistSession(ServerRequestInterface $request): void
    {
        $this->restoreFromRequest($request)?->persist();
    }

    public function restoreFromRequest(ServerRequestInterface $request): ?Session
    {
        if ($this->sessions->offsetExists($request)) {
            return $this->sessions->offsetGet($request);
        }

        $cookies = $request->getCookieParams();

        if (!isset($cookies[$this->sessionConfiguration->cookieName])) {
            return null;
        }

        /**
         * @var string
         */
        $sessionId = $cookies[$this->sessionConfiguration->cookieName];

        if (!uuid_is_valid($sessionId)) {
            return null;
        }

        return $this->freshSession($request, $sessionId);
    }

    public function setSessionCookie(Response $response, Session $session): void
    {
        $response->cookie(
            expires: time() + $this->sessionConfiguration->cookieLifespan,
            httponly: true,
            name: $this->sessionConfiguration->cookieName,
            samesite: $this->sessionConfiguration->cookieSameSite,
            secure: true,
            value: $session->id,
        );
    }

    public function start(ServerRequestInterface $request, Response $response): Session
    {
        $session = $this->restoreFromRequest($request) ?? $this->createSession($request);

        $this->setSessionCookie($response, $session);

        return $session;
    }

    private function createSession(ServerRequestInterface $request): Session
    {
        return $this->freshSession($request, uuid_create());
    }

    private function freshSession(ServerRequestInterface $request, string $sessionId): Session
    {
        $session = new Session(
            $this->redisConfiguration,
            $this->redisConnectionPoolRepository,
            $this->sessionConfiguration,
            $sessionId,
        );

        $this->sessions->offsetSet($request, $session);

        return $session;
    }
}
