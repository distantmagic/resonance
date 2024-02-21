<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\ProvidesAuthenticatedUser;
use Distantmagic\Resonance\Attribute\Singleton;
use Swoole\Http\Request;
use Swoole\Http\Response;
use WeakMap;

#[GrantsFeature(Feature::HttpSession)]
#[ProvidesAuthenticatedUser(1000)]
#[Singleton(collection: SingletonCollection::AuthenticatedUserStore)]
final readonly class SessionAuthentication implements AuthenticatedUserStoreInterface
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

    public function clearAuthenticatedUser(Request $request): void
    {
        $this->sessionManager->restoreFromRequest($request)?->data->clear();
    }

    public function getAuthenticatedUser(Request $request): ?AuthenticatedUser
    {
        if ($this->authenticatedUsers->offsetExists($request)) {
            return new AuthenticatedUser(
                AuthenticatedUserSource::Session,
                $this->authenticatedUsers->offsetGet($request),
            );
        }

        $user = $this->doGetAuthenticatedUser($request);

        $this->authenticatedUsers->offsetSet($request, $user);

        if (is_null($user)) {
            return null;
        }

        return new AuthenticatedUser(AuthenticatedUserSource::Session, $user);
    }

    public function setAuthenticatedUser(Request $request, Response $response, UserInterface $user): void
    {
        $session = $this->sessionManager->start($request, $response);
        $session->data->put('authenticated_user_id', (string) $user->getIdentifier());

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

        $userId = (string) $session->data->get('authenticated_user_id');

        if (!$userId) {
            return null;
        }

        return $this->userRepository->findUserById($userId);
    }
}
