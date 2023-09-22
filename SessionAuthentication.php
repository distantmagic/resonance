<?php

declare(strict_types=1);

namespace Resonance;

use Resonance\Attribute\Singleton;
use Swoole\Http\Request;
use Swoole\Http\Response;
use WeakMap;

#[Singleton]
final readonly class SessionAuthentication
{
    /**
     * @var WeakMap<Request, ?UserInterface>
     */
    private WeakMap $authenticatedUsers;

    public function __construct(
        private SessionManager $sessionManager,
        private UserRepositoryInterface $userRepository,
    ) {
        /**
         * @var WeakMap<Request, ?UserInterface>
         */
        $this->authenticatedUsers = new WeakMap();
    }

    public function authenticatedUser(Request $request): ?UserInterface
    {
        if ($this->authenticatedUsers->offsetExists($request)) {
            return $this->authenticatedUsers->offsetGet($request);
        }

        $user = $this->doGetAuthenticatedUser($request);

        $this->authenticatedUsers->offsetSet($request, $user);

        return $user;
    }

    public function clearAuthenticatedUser(Request $request): void
    {
        $session = $this->sessionManager->restoreFromRequest($request);

        if (!$session) {
            return;
        }

        $session->data->remove('authenticated_user_id');
    }

    public function setAuthenticatedUser(Request $request, Response $response, UserInterface $user): void
    {
        $session = $this->sessionManager->start($request, $response);
        $session->data->put('authenticated_user_id', $user->getId());

        $this->authenticatedUsers->offsetSet($request, $user);
    }

    private function doGetAuthenticatedUser(Request $request): ?UserInterface
    {
        $session = $this->sessionManager->restoreFromRequest($request);

        if (!$session) {
            return null;
        }

        if (!$session->data->hasKey('authenticated_user_id')) {
            return null;
        }

        $userId = (int) $session->data->get('authenticated_user_id');

        if (!$userId) {
            return null;
        }

        return $this->userRepository->findUserById($userId);
    }
}
