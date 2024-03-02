<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\Singleton;
use Ds\Map;
use Psr\Http\Message\ServerRequestInterface;

#[GrantsFeature(Feature::HttpSession)]
#[Singleton]
final readonly class CSRFManager
{
    public const REQUEST_BODY_KEY = 'csrf';
    public const SESSION_KEY = 'csrf';

    public function __construct(
        private readonly SecureIdentifierGenerator $secureIdentifierGenerator,
        private readonly SessionManager $sessionManager,
    ) {}

    public function checkToken(
        ServerRequestInterface $request,
        string $name,
        ?array $requestData,
    ): bool {
        if (is_null($requestData)) {
            return false;
        }

        if (!isset($requestData[self::REQUEST_BODY_KEY])) {
            return false;
        }

        $tokenStorage = $this->getTokenStorage($request);

        if (!$tokenStorage->hasKey($name)) {
            return false;
        }

        $isCorrect = $requestData[self::REQUEST_BODY_KEY] === $tokenStorage->get($name);

        // consume the token so it's not reused
        $tokenStorage->remove($name);

        return $isCorrect;
    }

    public function prepareSessionToken(
        ServerRequestInterface $request,
        string $name,
    ): string {
        $csrfToken = $this->secureIdentifierGenerator->generate();

        $this->getTokenStorage($request)->put($name, $csrfToken);

        return $csrfToken;
    }

    /**
     * @return Map<string,string>
     */
    private function getTokenStorage(ServerRequestInterface $request): Map
    {
        $session = $this->sessionManager->start($request);

        if (!$session->data->hasKey(self::SESSION_KEY)) {
            $session->data->put(self::SESSION_KEY, new Map());
        }

        $tokenStore = $session->data->get(self::SESSION_KEY);

        if (!($tokenStore instanceof Map)) {
            $updatedTokenStore = new Map();

            $session->data->remove(self::SESSION_KEY);
            $session->data->put(self::SESSION_KEY, $updatedTokenStore);

            return $updatedTokenStore;
        }

        /**
         * @var Map<string,string>
         */
        return $tokenStore;
    }
}
