<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\RespondsToHttp;

readonly class HttpResponderWithAttribute
{
    public function __construct(
        public HttpResponderInterface $httpResponder,
        public RespondsToHttp $respondsToHttp,
    ) {}
}
