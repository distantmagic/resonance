<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Link\LinkInterface as PsrLinkInterface;

interface LinkInterface extends PsrLinkInterface
{
    public function isTemplated(): false;
}
