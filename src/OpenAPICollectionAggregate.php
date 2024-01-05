<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use Ds\Set;

readonly class OpenAPICollectionAggregate
{
    /**
     * @var Map<OpenAPICollectionSymbolInterface,Set<OpenAPICollectionEndpoint>>
     */
    public Map $openAPICollections;

    public function __construct()
    {
        $this->openAPICollections = new Map();
    }

    public function registerCollectionEndpoint(OpenAPICollectionEndpoint $openAPICollectionEndpoint): void
    {
        $collectionSymbol = $openAPICollectionEndpoint->belongsToOpenAPICollection->collectionSymbol;

        if (!$this->openAPICollections->hasKey($collectionSymbol)) {
            $this->openAPICollections->put($collectionSymbol, new Set());
        }

        $this
            ->openAPICollections
            ->get($collectionSymbol)
            ->add($openAPICollectionEndpoint)
        ;
    }
}
