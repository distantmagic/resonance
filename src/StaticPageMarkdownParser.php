<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\Singleton;
use Embed\Embed;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Extension\DescriptionList\DescriptionListExtension;
use League\CommonMark\Extension\Embed\Bridge\OscaroteroEmbedAdapter;
use League\CommonMark\Extension\Embed\EmbedExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter;

#[GrantsFeature(Feature::StaticPages)]
#[Singleton]
readonly class StaticPageMarkdownParser
{
    public MarkdownConverter $converter;
    public Environment $environment;

    public function __construct(
        StaticPageAggregate $staticPageAggregate,
        StaticPageConfiguration $staticPageConfiguration,
    ) {
        $embed = new Embed();
        $embed->setSettings([
            'oembed:query_parameters' => [
                'maxwidth' => 800,
                'maxheight' => 600,
            ],
        ]);

        $this->environment = new Environment([
            'embed' => [
                'adapter' => new OscaroteroEmbedAdapter($embed),
                'allowed_domains' => [
                    'github.com',
                    'youtube.com',
                ],
                'fallback' => 'link',
            ],
            'external_link' => [
                'html_class' => 'external-link',
                'internal_hosts' => [
                    $staticPageConfiguration->baseUrl,
                ],
                'open_in_new_window' => true,
            ],
            'heading_permalink' => [
                'aria_hidden' => false,
                'insert' => 'before',
                'symbol' => '',
            ],
        ]);

        $this->environment->addExtension(new CommonMarkCoreExtension());
        $this->environment->addExtension(new DescriptionListExtension());
        $this->environment->addExtension(new EmbedExtension());
        $this->environment->addExtension(new ExternalLinkExtension());
        $this->environment->addExtension(new GithubFlavoredMarkdownExtension());
        $this->environment->addExtension(new HeadingPermalinkExtension());

        $this->environment->addExtension(new CommonMarkAdmonitionExtension());
        $this->environment->addExtension(new StaticPageInternalLinkMarkdownExtension($staticPageAggregate->staticPages));

        $this->environment->addRenderer(Code::class, new CommonMarkInlineCodeRenderer());
        $this->environment->addRenderer(FencedCode::class, new CommonMarkFencedCodeRenderer());
        $this->environment->addRenderer(CommonMarkAdmonitionBlock::class, new CommonMarkAdmonitionRenderer());

        $this->converter = new MarkdownConverter($this->environment);
    }

    public function toHtml(string $content): string
    {
        return $this->converter->convert($content)->getContent();
    }
}
