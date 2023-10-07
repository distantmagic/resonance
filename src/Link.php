<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class Link implements LinkInterface
{
    use LinkTrait;

    public function isTemplated(): false
    {
        return false;
    }
}
