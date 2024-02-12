<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Ds\Map;

readonly class RespondsToHttpAttributeCollection
{
    /**
     * @var Map<class-string<HttpResponderInterface>, PHPFileReflectionClassAttribute<HttpResponderInterface,RespondsToHttp>> $attributes
     */
    public Map $attributes;

    public function __construct()
    {
        $this->attributes = new Map();
    }
}
