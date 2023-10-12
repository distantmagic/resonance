<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class ApplicationContext
{
    public function __construct(public Environment $environment) {}
}
