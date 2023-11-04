<?php

namespace Distantmagic\Resonance;

use Twig\Loader\LoaderInterface;

interface TwigOptionalLoaderInterface extends LoaderInterface
{
    public function beforeRegister(): void;

    public function shouldRegister(): bool;
}
