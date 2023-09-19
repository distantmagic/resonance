<?php

declare(strict_types=1);

namespace Resonance;

use Ds\Map;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter;
use Spatie\CommonMarkHighlighter\FencedCodeRenderer;
use Spatie\CommonMarkHighlighter\IndentedCodeRenderer;

readonly class StaticPageMarkdownParser
{
    public MarkdownConverter $converter;
    public Environment $environment;

    /**
     * @param Map<string, StaticPage> $staticPages
     */
    public function __construct(Map $staticPages)
    {
        $this->environment = new Environment([
            'external_link' => [
                'html_class' => 'external-link',
                'internal_hosts' => [
                    DM_STATIC_BASE_URL,
                ],
                'open_in_new_window' => true,
            ],
            'heading_permalink' => [
                'aria_hidden' => false,
                'insert' => 'before',
                'symbol' => '»',
            ],
        ]);

        $this->environment->addExtension(new CommonMarkCoreExtension());
        $this->environment->addExtension(new ExternalLinkExtension());
        $this->environment->addExtension(new GithubFlavoredMarkdownExtension());
        $this->environment->addExtension(new HeadingPermalinkExtension());

        $this->environment->addExtension(new CommonMarkAdmonitionExtension());
        $this->environment->addExtension(new CommonMarkTabletOfContentsExtension());
        $this->environment->addExtension(new StaticPageInternalLinkMarkdownExtension($staticPages));

        $this->environment->addRenderer(FencedCode::class, new FencedCodeRenderer());
        $this->environment->addRenderer(IndentedCode::class, new IndentedCodeRenderer());
        $this->environment->addRenderer(CommonMarkAdmonitionBlock::class, new CommonMarkAdmonitionRenderer());

        $this->converter = new MarkdownConverter($this->environment);
    }

    public function toHtml(string $content): string
    {
        return $this->converter->convert($content)->getContent();
    }
}
