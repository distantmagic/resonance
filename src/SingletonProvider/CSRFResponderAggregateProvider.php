<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\ValidatesCSRFToken;
use Distantmagic\Resonance\CSRFResponderAggregate;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<CSRFResponderAggregate>
 */
#[Singleton(
    provides: CSRFResponderAggregate::class,
    requiresCollection: SingletonCollection::HttpResponder,
)]
final readonly class CSRFResponderAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): CSRFResponderAggregate
    {
        $siteActionGateAggregate = new CSRFResponderAggregate();

        foreach ($this->collectResponders($singletons) as $deciderAttribute) {
            $siteActionGateAggregate->httpResponders->put(
                $deciderAttribute->singleton,
                $deciderAttribute->attribute->requestDataSource,
            );
        }

        return $siteActionGateAggregate;
    }

    /**
     * @return iterable<SingletonAttribute<HttpResponderInterface,ValidatesCSRFToken>>
     */
    private function collectResponders(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            HttpResponderInterface::class,
            ValidatesCSRFToken::class,
        );
    }
}
