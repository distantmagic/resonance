<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\Singleton;

#[GrantsFeature(Feature::StaticPages)]
#[Singleton]
readonly class StaticPageContentRenderer
{
    public function __construct(private StaticPageMarkdownParser $markdownParser) {}

    public function renderContent(StaticPage $staticPage): string
    {
        return match ($staticPage->frontMatter->contentType) {
            StaticPageContentType::Markdown => $this->markdownParser->toHtml($staticPage->content),
            StaticPageContentType::Html => $staticPage->content,
        };
    }
}
