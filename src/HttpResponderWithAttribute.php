<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\RespondsToHttp;
use ReflectionClass;
use ReflectionFunction;

readonly class HttpResponderWithAttribute implements ReflectionAttributeInterface
{
    /**
     * @param ReflectionClass<HttpResponderInterface>|ReflectionFunction $reflection
     */
    public function __construct(
        public HttpResponderInterface $httpResponder,
        public ReflectionClass|ReflectionFunction $reflection,
        public RespondsToHttp $respondsToHttp,
    ) {}

    public function getReflectionAttributeManager(): ReflectionAttributeManager
    {
        return new ReflectionAttributeManager($this->reflection);
    }
}
