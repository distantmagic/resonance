<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\ControlsWebSocketProtocol;
use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Distantmagic\Resonance\WebSocketProtocolControllerAggregate;
use Distantmagic\Resonance\WebSocketProtocolControllerInterface;

/**
 * @template-extends SingletonProvider<WebSocketProtocolControllerAggregate>
 */
#[RequiresSingletonCollection(SingletonCollection::WebSocketProtocolController)]
#[Singleton(provides: WebSocketProtocolControllerAggregate::class)]
final readonly class WebSocketProtocolControllerAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): WebSocketProtocolControllerAggregate
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
