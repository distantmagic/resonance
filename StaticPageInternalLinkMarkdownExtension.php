<?php

declare(strict_types=1);

namespace Resonance;

use Ds\Map;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

readonly class StaticPageInternalLinkMarkdownExtension implements ExtensionInterface
{
    /**
     * @param Map<string, StaticPage> $staticPages
     */
    public function __construct(private Map $staticPages) {}

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addDelimiterProcessor(new StaticPageInternalLinkDelimiterProcessor());
        $environment->addRenderer(
            StaticPageInternalLinkNode::class,
            new StaticPageInternalLinkNodeRenderer($this->staticPages),
        );
    }
}
