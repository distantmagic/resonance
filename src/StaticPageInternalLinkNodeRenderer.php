<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use Ds\Set;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use Stringable;

readonly class StaticPageInternalLinkNodeRenderer implements NodeRendererInterface
{
    /**
     * @param Map<string, StaticPage> $staticPages
     */
    public function __construct(private Map $staticPages) {}

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): null|string|Stringable
    {
        StaticPageInternalLinkNode::assertInstanceOf($node);

        $staticPages = $this->getStaticPages($node, $childRenderer);

        if (!($staticPages instanceof Set)) {
            return $this->renderStaticPageLink($staticPages);
        }

        return new HtmlElement(
            'div',
            [
                'class' => 'document-links-group',
            ],
            $staticPages->map($this->renderStaticPageBlockLink(...))->toArray(),
        );
    }

    private function compareStaticPages(StaticPage $a, StaticPage $b): int
    {
        return $a->compare($b);
    }

    private function getPageBasename(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        return $childRenderer->renderNodes($node->children());
    }

    /**
     * @return Set<StaticPage>|StaticPage
     */
    private function getStaticPages(Node $node, ChildNodeRendererInterface $childRenderer): Set|StaticPage
    {
        $pageBasename = $this->getPageBasename($node, $childRenderer);

        if (str_contains($pageBasename, '*')) {
            return $this->getStaticPagesByPattern($pageBasename);
        }

        if (!$this->staticPages->hasKey($pageBasename)) {
            throw new StaticPageReferenceException('The page contains a link to another document that does not exist and thus cannot be rendered. The invalid link: {{'.$pageBasename.'}}');
        }

        return $this->staticPages->get($pageBasename);
    }

    /**
     * @return Set<StaticPage>
     */
    private function getStaticPagesByPattern(string $pattern): Set
    {
        /**
         * @var Set<StaticPage>
         */
        $ret = new Set();

        $chunks = explode('!', $pattern);

        $pattern = $chunks[0];
        $skip = $chunks[1] ?? null;

        foreach ($this->staticPages as $baseUrl => $staticPage) {
            if (!fnmatch($pattern, $baseUrl, FNM_PATHNAME)) {
                continue;
            }
            if ($baseUrl === $skip) {
                continue;
            }
            $ret->add($staticPage);
        }

        if ($ret->isEmpty()) {
            throw new StaticPageReferenceException('There is no static page that matches pattern: '.$pattern);
        }

        $ret->sort($this->compareStaticPages(...));

        return $ret;
    }

    private function renderStaticPageBlockLink(StaticPage $staticPage): Stringable
    {
        /**
         * @list<HtmlElement> $content
         */
        $content = [
            new HtmlElement(
                'div',
                [
                    'class' => 'document-links-group__link__title',
                ],
                $staticPage->frontMatter->title
            ),
            new HtmlElement(
                'div',
                [
                    'class' => 'document-links-group__link__description',
                ],
                $staticPage->frontMatter->description
            ),
        ];

        if (!empty($staticPage->frontMatter->tags)) {
            $tags = [];

            foreach ($staticPage->frontMatter->tags as $tag) {
                $tags[] = new HtmlElement(
                    'li',
                    [
                        'class' => 'document-links-group__link__tag',
                    ],
                    $tag,
                );
            }

            $content[] = new HtmlElement(
                'ol',
                [
                    'class' => 'document-links-group__link__tags',
                ],
                $tags
            );
        }

        return new HtmlElement(
            'a',
            [
                'class' => 'document-links-group__link',
                'href' => $staticPage->getHref(),
            ],
            $content,
        );
    }

    private function renderStaticPageLink(StaticPage $staticPage): Stringable
    {
        return new HtmlElement(
            'a',
            [
                'href' => $staticPage->getHref(),
            ],
            $staticPage->frontMatter->title,
        );
    }
}
