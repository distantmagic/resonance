<?php

declare(strict_types=1);

namespace Resonance;

readonly class Link implements LinkInterface
{
    use LinkTrait;

    public function isTemplated(): false
    {
        return false;
    }
}
