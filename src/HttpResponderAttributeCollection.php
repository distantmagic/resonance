<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Ds\Set;

readonly class HttpResponderAttributeCollection
{
    /**
     * @var Set<PHPFileReflectionClassAttribute<HttpResponderInterface,RespondsToHttp>> $httpResponderAttributes
     */
    public Set $httpResponderAttributes;

    public function __construct()
    {
        $this->httpResponderAttributes = new Set();
    }
}
