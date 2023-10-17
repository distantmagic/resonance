<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use Ds\Set;

readonly class HttpPreprocessorAggregate
{
    /**
     * @var Map<HttpResponderInterface,Set<HttpPreprocessorAttribute>>
     */
    public Map $preprocessors;

    public function __construct()
    {
        $this->preprocessors = new Map();
    }

    public function registerPreprocessor(
        HttpPreprocessorInterface $httpPreprocessor,
        HttpResponderInterface $httpResponder,
        Attribute $attribute,
    ): void {
        if (!$this->preprocessors->hasKey($httpResponder)) {
            $this->preprocessors->put($httpResponder, new Set());
        }

        $this->preprocessors->get($httpResponder)->add(new HttpPreprocessorAttribute(
            $httpPreprocessor,
            $attribute,
        ));
    }
}
