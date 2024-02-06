<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\Singleton;
use Generator;
use IteratorAggregate;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock;
use League\CommonMark\Node\Node;
use League\CommonMark\Node\StringContainerHelper;

/**
 * @template-implements IteratorAggregate<non-empty-string>
 */
#[GrantsFeature(Feature::StaticPages)]
#[Singleton]
readonly class StaticPageChunkIterator implements IteratorAggregate
{
    public function __construct(
        private StaticPageAggregate $staticPageAggregate,
        private StaticPageMarkdownParser $staticPageMarkdownParser,
    ) {}

    /**
     * @return Generator<non-empty-string>
     */
    public function getIterator(): Generator
    {
        foreach ($this->staticPageAggregate->staticPages as $staticPage) {
            if (StaticPageContentType::Html === $staticPage->frontMatter->contentType) {
                continue;
            }

            $document = $this
                ->staticPageMarkdownParser
                ->converter
                ->convert($staticPage->content)
                ->getDocument()
            ;

            yield from $this->generateChunksFromNodeChildren($document);
        }
    }

    private function extractNodeTextContent(Node $node): string
    {
        $childTextContent = StringContainerHelper::getChildText($node);

        return trim(strip_tags($childTextContent));
    }

    /**
     * @return Generator<non-empty-string>
     */
    private function generateChunksFromNodeChildren(Node $node): Generator
    {
        foreach ($node->children() as $child) {
            if ($child instanceof Heading) {
                continue;
            }

            if ($child instanceof FencedCode) {
                continue;
            }

            if ($child instanceof StaticPageInternalLinkNode) {
                continue;
            }

            if ($child instanceof ListBlock) {
                yield from $this->generateChunksFromNodeChildren($child);

                continue;
            }

            $textContent = $this->extractNodeTextContent($child);
            if (empty($textContent)) {
                continue;
            }
            if (!str_contains($textContent, ' ')) {
                continue;
            }
            yield $textContent;
        }
    }
}
