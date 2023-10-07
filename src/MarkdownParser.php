<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;

readonly class MarkdownParser
{
    private MarkdownConverter $converter;

    public function __construct()
    {
        $environment = new Environment();

        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());

        $this->converter = new MarkdownConverter($environment);
    }

    public function toHtml(string $content): string
    {
        return $this->converter->convert($content)->getContent();
    }
}
