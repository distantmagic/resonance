<?php

declare(strict_types=1);

namespace Resonance;

use App\DatabaseEntity\User;
use App\DatabaseQuery\SelectUserById;
use Psr\Log\LoggerInterface;
use Resonance\Attribute\Singleton;
use Swoole\Database\PDOPool;
use Swoole\Http\Request;
use Swoole\Http\Response;
use WeakMap;

#[Singleton]
final readonly class SessionAuthentication
{
    /**
     * @var WeakMap<Request, ?User>
     */
    private WeakMap $authenticatedUsers;

    public function __construct(
        private LoggerInterface $logger,
        private PDOPool $pdoPool,
        private SessionManager $sessionManager,
    ) {
        /**
         * @var WeakMap<Request, ?User>
         */
        $this->authenticatedUsers = new WeakMap();
    }

    public function authenticatedUser(Request $request): ?User
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

    public function setAuthenticatedUser(Request $request, Response $response, User $user): void
    {
        $session = $this->sessionManager->start($request, $response);
        $session->data->put('authenticated_user_id', $user->id);

        $this->authenticatedUsers->offsetSet($request, $user);
    }

    private function doGetAuthenticatedUser(Request $request): ?User
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

        $selectUserById = new SelectUserById(
            $this->logger,
            $this->pdoPool,
            $userId,
        );

        return $selectUserById->execute();
    }
}
