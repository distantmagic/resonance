<?php

declare(strict_types=1);

namespace Resonance;

use League\CommonMark\Node\Inline\AbstractInline;
use League\CommonMark\Node\Inline\DelimitedInterface;

class StaticPageInternalLinkNode extends AbstractInline implements DelimitedInterface
{
    public function getClosingDelimiter(): string
    {
        return '}}';
    }

    public function getOpeningDelimiter(): string
    {
        return '{{';
    }
}
