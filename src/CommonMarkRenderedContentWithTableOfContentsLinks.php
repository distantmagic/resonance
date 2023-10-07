<?php

declare(strict_types=1);

namespace Resonance;

use League\CommonMark\Node\Block\Document;
use League\CommonMark\Output\RenderedContent;

class CommonMarkRenderedContentWithTableOfContentsLinks extends RenderedContent
{
    /**
     * @param array<CommonMarkTableOfContentsLink> $tableOfContentsLinks
     */
    public function __construct(
        Document $document,
        string $content,
        public readonly array $tableOfContentsLinks
    ) {
        parent::__construct($document, $content);
    }
}
