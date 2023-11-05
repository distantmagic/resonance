<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Twig\Loader\LoaderInterface;

interface TwigOptionalLoaderInterface extends LoaderInterface, RegisterableInterface
{
    public function beforeRegister(): void;
}
