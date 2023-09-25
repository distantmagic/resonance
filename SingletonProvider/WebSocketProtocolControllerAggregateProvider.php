<?php

declare(strict_types=1);

namespace Resonance\SingletonProvider;

use Resonance\Attribute\ControlsWebSocketProtocol;
use Resonance\Attribute\Singleton;
use Resonance\SingletonAttribute;
use Resonance\SingletonCollection;
use Resonance\SingletonContainer;
use Resonance\SingletonProvider;
use Resonance\WebSocketProtocolControllerAggregate;
use Resonance\WebSocketProtocolControllerInterface;

/**
 * @template-extends SingletonProvider<WebSocketProtocolControllerAggregate>
 */
#[Singleton(
    provides: WebSocketProtocolControllerAggregate::class,
    requiresCollection: SingletonCollection::WebSocketProtocolController,
)]
final readonly class WebSocketProtocolControllerAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons): WebSocketProtocolControllerAggregate
    {
        $controllerAggregate = new WebSocketProtocolControllerAggregate();

        foreach ($this->collectWebSocketProtocolControllers($singletons) as $protocolControllerAttribute) {
            $controllerAggregate->protocolControllers->put(
                $protocolControllerAttribute->attribute->webSocketProtocol,
                $protocolControllerAttribute->singleton,
            );
        }

        return $controllerAggregate;
    }

    /**
     * @return iterable<SingletonAttribute<WebSocketProtocolControllerInterface,ControlsWebSocketProtocol>>
     */
    private function collectWebSocketProtocolControllers(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            WebSocketProtocolControllerInterface::class,
            ControlsWebSocketProtocol::class,
        );
    }
}
