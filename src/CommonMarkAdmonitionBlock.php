<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use League\CommonMark\Node\Block\AbstractBlock;

final class CommonMarkAdmonitionBlock extends AbstractBlock
{
    public function __construct(public readonly string $fenceType)
    {
        parent::__construct();
    }
}
