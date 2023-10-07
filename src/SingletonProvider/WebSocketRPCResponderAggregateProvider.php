<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RespondsToWebSocketRPC;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Distantmagic\Resonance\WebSocketRPCResponderAggregate;
use Distantmagic\Resonance\WebSocketRPCResponderInterface;

/**
 * @template-extends SingletonProvider<WebSocketRPCResponderAggregate>
 */
#[Singleton(
    provides: WebSocketRPCResponderAggregate::class,
    requiresCollection: SingletonCollection::WebSocketRPCResponder,
)]
final readonly class WebSocketRPCResponderAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): WebSocketRPCResponderAggregate
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
