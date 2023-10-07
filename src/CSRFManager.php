<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Swoole\Http\Request;
use Swoole\Http\Response;

#[Singleton]
final readonly class CSRFManager
{
    public const REQUEST_BODY_KEY = 'csrf';
    public const SESSION_KEY = 'csrf';

    public function __construct(
        private readonly SessionManager $sessionManager,
    ) {}

    public function checkToken(
        Request $request,
        ?array $requestData,
    ): bool {
        if (is_null($requestData)) {
            return false;
        }

        $session = $this->sessionManager->restoreFromRequest($request);

        if (!$session) {
            return false;
        }

        if (!isset($requestData[self::REQUEST_BODY_KEY])) {
            return false;
        }

        if (!$session->data->hasKey(self::SESSION_KEY)) {
            return false;
        }

        $isCorrect = $requestData[self::REQUEST_BODY_KEY] === $session->data->get(self::SESSION_KEY);

        // consume the token so it's not reused
        $session->data->remove(self::SESSION_KEY);

        return $isCorrect;
    }

    public function prepareSessionToken(Request $request, Response $response): string
    {
        $session = $this->sessionManager->start($request, $response);

        $csrfToken = bin2hex(random_bytes(20));

        $session->data->put(self::SESSION_KEY, $csrfToken);

        return $csrfToken;
    }
}
