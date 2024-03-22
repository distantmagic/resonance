<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\RespondsToWebSocketJsonRPC;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Distantmagic\Resonance\WebSocketJsonRPCResponderAggregate;
use Distantmagic\Resonance\WebSocketJsonRPCResponderInterface;

/**
 * @template-extends SingletonProvider<WebSocketJsonRPCResponderAggregate>
 */
#[RequiresSingletonCollection(SingletonCollection::WebSocketJsonRPCResponder)]
#[Singleton(provides: WebSocketJsonRPCResponderAggregate::class)]
final readonly class WebSocketJsonRPCResponderAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): WebSocketJsonRPCResponderAggregate
    {
        $webSocketJsonRPCResponderAggregate = new WebSocketJsonRPCResponderAggregate();

        foreach ($this->collectWebSocketJsonRPCResponders($singletons) as $rpcResponderAttribute) {
            $webSocketJsonRPCResponderAggregate->cachedConstraints->put(
                $rpcResponderAttribute->singleton,
                $rpcResponderAttribute->singleton->getConstraint(),
            );
            $webSocketJsonRPCResponderAggregate->rpcResponders->put(
                $rpcResponderAttribute->attribute->method,
                $rpcResponderAttribute->singleton,
            );
        }

        return $webSocketJsonRPCResponderAggregate;
    }

    /**
     * @return iterable<SingletonAttribute<WebSocketJsonRPCResponderInterface,RespondsToWebSocketJsonRPC>>
     */
    private function collectWebSocketJsonRPCResponders(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            WebSocketJsonRPCResponderInterface::class,
            RespondsToWebSocketJsonRPC::class,
        );
    }
}
