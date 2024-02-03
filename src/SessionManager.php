<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\ListensTo;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Event\HttpResponseReady;
use Swoole\Http\Request;
use Swoole\Http\Response;
use WeakMap;

/**
 * @template-extends EventListener<HttpResponseReady,void>
 */
#[ListensTo(HttpResponseReady::class)]
#[Singleton(collection: SingletonCollection::EventListener)]
final readonly class SessionManager extends EventListener
{
    /**
     * @var WeakMap<Request, Session>
     */
    private WeakMap $sessions;

    public function __construct(
        private RedisConfiguration $redisConfiguration,
        private RedisConnectionPoolRepository $redisConnectionPoolRepository,
        private SessionConfiguration $sessionConfiguration,
    ) {
        /**
         * @var WeakMap<Request, Session>
         */
        $this->sessions = new WeakMap();
    }

    /**
     * @param HttpResponseReady $event
     */
    public function handle(object $event): void
    {
        $this->persistSession($event->request);
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

        if (
            !is_array($request->cookie)
            || !isset($request->cookie[$this->sessionConfiguration->cookieName])
        ) {
            return null;
        }

        /**
         * @var string
         */
        $sessionId = $request->cookie[$this->sessionConfiguration->cookieName];

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
