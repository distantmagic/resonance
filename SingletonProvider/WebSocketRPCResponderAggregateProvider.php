<?php

declare(strict_types=1);

namespace Resonance\SingletonProvider;

use Resonance\Attribute\RespondsToWebSocketRPC;
use Resonance\Attribute\Singleton;
use Resonance\SingletonAttribute;
use Resonance\SingletonCollection;
use Resonance\SingletonContainer;
use Resonance\SingletonProvider;
use Resonance\WebSocketRPCResponderAggregate;
use Resonance\WebSocketRPCResponderInterface;

/**
 * @template-extends SingletonProvider<WebSocketRPCResponderAggregate>
 */
#[Singleton(
    provides: WebSocketRPCResponderAggregate::class,
    requiresCollection: SingletonCollection::WebSocketRPCResponder,
)]
final readonly class WebSocketRPCResponderAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons): WebSocketRPCResponderAggregate
    {
        $webSocketRPCResponderAggregate = new WebSocketRPCResponderAggregate();

        foreach ($this->collectWebSocketRPCResponders($singletons) as $rpcResponderAttribute) {
            $webSocketRPCResponderAggregate->rpcResponders->put(
                $rpcResponderAttribute->attribute->method,
                $rpcResponderAttribute->singleton,
            );
        }

        return $webSocketRPCResponderAggregate;
    }

    /**
     * @return iterable<SingletonAttribute<WebSocketRPCResponderInterface,RespondsToWebSocketRPC>>
     */
    private function collectWebSocketRPCResponders(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            WebSocketRPCResponderInterface::class,
            RespondsToWebSocketRPC::class,
        );
    }
}
