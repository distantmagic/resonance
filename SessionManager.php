<?php

declare(strict_types=1);

namespace Resonance;

use Resonance\Attribute\Singleton;
use Swoole\Database\RedisPool;
use Swoole\Http\Request;
use Swoole\Http\Response;
use WeakMap;

#[Singleton]
final readonly class SessionManager
{
    /**
     * @var WeakMap<Request, Session>
     */
    private WeakMap $sessions;

    public function __construct(private RedisPool $redisPool)
    {
        /**
         * @var WeakMap<Request, Session>
         */
        $this->sessions = new WeakMap();
    }

    public function persistSession(Request $request): void
    {
        $this->restoreFromRequest($request)?->persist();
    }

    public function restoreFromRequest(Request $request): ?Session
    {
        if ($this->sessions->offsetExists($request)) {
            return $this->sessions->offsetGet($request);
        }

        if (!is_array($request->cookie) || !isset($request->cookie[DM_SESSSION_COOKIE_NAME])) {
            return null;
        }

        /**
         * @var string
         */
        $sessionId = $request->cookie[DM_SESSSION_COOKIE_NAME];

        if (!uuid_is_valid($sessionId)) {
            return null;
        }

        return $this->freshSession($request, $sessionId);
    }

    public function setSessionCookie(Response $response, Session $session): void
    {
        $response->cookie(
            expires: time() + 60 * 60 * 24,
            httponly: true,
            name: DM_SESSSION_COOKIE_NAME,
            samesite: 'strict',
            secure: true,
            value: $session->id,
        );
    }

    public function start(Request $request, Response $response): Session
    {
        $session = $this->restoreFromRequest($request) ?? $this->createSession($request);

        $this->setSessionCookie($response, $session);

        return $session;
    }

    private function createSession(Request $request): Session
    {
        return $this->freshSession($request, uuid_create());
    }

    private function freshSession(Request $request, string $sessionId): Session
    {
        $session = new Session($this->redisPool, $sessionId);

        $this->sessions->offsetSet($request, $session);

        return $session;
    }
}