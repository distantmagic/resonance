<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use HTMLPurifier;
use Distantmagic\Resonance\Attribute\Singleton;

#[Singleton]
final readonly class SanitizedMarkdownParser
{
    private MarkdownParser $markdownParser;

    public function __construct(
        private readonly HTMLPurifier $purifier,
    ) {
        $this->markdownParser = new MarkdownParser();
    }

    public function toHtml(string $content): string
    {
        $text = $this->markdownParser->toHtml($content);

        return $this->purifier->purify($text);
    }
}
