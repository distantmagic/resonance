<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\WebSocketAware;
use Doctrine\ORM\EntityManagerInterface;
use Ds\Map;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Coroutine;
use Swoole\Coroutine\Context;
use WeakMap;

#[Singleton(collection: SingletonCollection::WebSocketAware)]
#[WebSocketAware]
readonly class DoctrineEntityManagerWeakStore implements WebSocketAwareInterface
{
    /**
     * @var WeakMap<ServerRequestInterface|WebSocketConnection,Map<string,EntityManagerInterface>>
     */
    private WeakMap $entityManagers;

    public function __construct()
    {
        /**
         * @var WeakMap<ServerRequestInterface|WebSocketConnection,Map<string,EntityManagerInterface>>
         */
        $this->entityManagers = new WeakMap();
    }

    /**
     * @param non-empty-string $name
     */
    public function fromContext(string $name): ?EntityManagerWeakReference
    {
        $context = Coroutine::getContext();

        if (!$context instanceof Context) {
            return null;
        }

        $contextKey = $this->createContextKey('connection.'.$name);

        if (isset($context[$contextKey]) && $context[$contextKey] instanceof EntityManagerWeakReference) {
            return $context[$contextKey];
        }

        return null;
    }

    /**
     * @param non-empty-string $name
     */
    public function fromRequest(ServerRequestInterface|WebSocketConnection $request, string $name): ?EntityManagerInterface
    {
        $entityManagers = $this->getEntityManagersByRequest($request);

        if ($entityManagers->hasKey($name)) {
            return $entityManagers->get($name);
        }

        return null;
    }

    public function onHttpConnectionUpgraded(ServerRequestInterface $request, WebSocketConnection $webSocketConnection): void
    {
        /**
         * @var null|Context
         */
        $context = Coroutine::getContext();

        if ($context instanceof Context) {
            $connectionIndexKey = $this->createContextKey('connection_index');

            if (isset($context[$connectionIndexKey])) {
                /**
                 * @var mixed $connectionIndex explicitly mixed for typechecks
                 */
                $connectionIndex = $context[$connectionIndexKey];

                if (is_array($connectionIndex)) {
                    /**
                     * @var mixed $connectionName explicitly mixed for typechecks
                     */
                    foreach ($connectionIndex as $connectionName) {
                        if (!is_string($connectionName) || empty($connectionName)) {
                            continue;
                        }

                        $entityManagerWeakReference = $this->fromContext($connectionName);

                        if (!$entityManagerWeakReference) {
                            continue;
                        }

                        $this->setByRequest(
                            $webSocketConnection,
                            $connectionName,
                            $entityManagerWeakReference->getEntityManager(),
                        );
                    }
                }
            }
        }

        if (!$this->entityManagers->offsetExists($request)) {
            return;
        }

        $entityManagers = $this->entityManagers->offsetGet($request);

        $this->entityManagers->offsetUnset($request);
        $this->entityManagers->offsetSet($webSocketConnection, $entityManagers);
    }

    public function setByRequest(ServerRequestInterface|WebSocketConnection $request, string $name, EntityManagerInterface $entityManager): void
    {
        $this->getEntityManagersByRequest($request)->put($name, $entityManager);
    }

    /**
     * @param non-empty-string $name
     */
    public function setInContext(string $name, EntityManagerWeakReference $entityManagerWeakReference): void
    {
        $context = Coroutine::getContext();

        if (!$context instanceof Context) {
            return;
        }

        $context[$this->createContextKey('connection.'.$name)] = $entityManagerWeakReference;

        $connectionIndexKey = $this->createContextKey('connection_index');

        if (isset($context[$connectionIndexKey])) {
            /**
             * @var mixed $connectionIndex explicitly mixed for typechecks
             */
            $connectionIndex = $context[$connectionIndexKey];

            if (is_array($connectionIndex)) {
                $connectionIndex[] = $name;
            } else {
                $connectionIndex = [$name];
            }

            $context[$connectionIndexKey] = array_unique($connectionIndex);
        } else {
            $context[$connectionIndexKey] = [$name];
        }
    }

    /**
     * @param non-empty-string $name
     */
    private function createContextKey(string $name): string
    {
        return sprintf('%s.%s', self::class, $name);
    }

    /**
     * @return Map<string,EntityManagerInterface>
     */
    private function getEntityManagersByRequest(ServerRequestInterface|WebSocketConnection $request): Map
    {
        if (!$this->entityManagers->offsetExists($request)) {
            $this->entityManagers->offsetSet($request, new Map());
        }

        return $this->entityManagers->offsetGet($request);
    }
}
