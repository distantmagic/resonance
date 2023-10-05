<?php

declare(strict_types=1);

namespace Resonance\SingletonProvider;

use Resonance\Attribute\Singleton;
use Resonance\Attribute\ValidatesCSRFToken;
use Resonance\CSRFResponderAggregate;
use Resonance\HttpResponderInterface;
use Resonance\PHPProjectFiles;
use Resonance\SingletonAttribute;
use Resonance\SingletonCollection;
use Resonance\SingletonContainer;
use Resonance\SingletonProvider;

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
