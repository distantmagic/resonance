<?php

declare(strict_types=1);

namespace Resonance;

use Ds\Map;

readonly class StaticPageContentRenderer
{
    public StaticPageMarkdownParser $markdownParser;

    /**
     * @param Map<string, StaticPage> $staticPages
     */
    public function __construct(Map $staticPages)
    {
        $this->markdownParser = new StaticPageMarkdownParser($staticPages);
    }

    public function renderContent(StaticPage $staticPage): string
    {
        return match ($staticPage->frontMatter->contentType) {
            StaticPageContentType::Markdown => $this->markdownParser->toHtml($staticPage->content),
            StaticPageContentType::Html => $staticPage->content,
        };
    }
}
