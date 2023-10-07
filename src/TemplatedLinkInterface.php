<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Link\LinkInterface as PsrLinkInterface;

interface TemplatedLinkInterface extends PsrLinkInterface
{
    /**
     * @param null|array<string, string> $params
     */
    public function build(?array $params = null): LinkInterface;

    /**
     * @param null|array<string, string> $params
     */
    public function buildHref(?array $params = null): string;

    public function isTemplated(): true;
}
