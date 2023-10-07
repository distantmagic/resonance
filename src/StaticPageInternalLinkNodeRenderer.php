<?php

declare(strict_types=1);

namespace Resonance;

use Ds\Map;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

readonly class StaticPageInternalLinkNodeRenderer implements NodeRendererInterface
{
    /**
     * @param Map<string, StaticPage> $staticPages
     */
    public function __construct(private Map $staticPages) {}

    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        StaticPageInternalLinkNode::assertInstanceOf($node);

        $staticPage = $this->getStaticPage($node, $childRenderer);
        $attributes = [
            'href' => $staticPage->getHref(),
        ];

        return new HtmlElement('a', $attributes, $staticPage->frontMatter->title);
    }

    private function getPageBasename(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        return $childRenderer->renderNodes($node->children());
    }

    private function getStaticPage(Node $node, ChildNodeRendererInterface $childRenderer): StaticPage
    {
        $pageBasename = $this->getPageBasename($node, $childRenderer);

        if (!$this->staticPages->hasKey($pageBasename)) {
            throw new StaticPageReferenceException('The page contains a link to another document that does not exist and thus cannot be rendered. The invalid link: {{'.$pageBasename.'}}');
        }

        return $this->staticPages->get($pageBasename);
    }
}
