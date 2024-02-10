<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\Singleton;
use Generator;
use IteratorAggregate;
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
        $textContent = $this->extractNodeTextContent($node);

        if (!empty($textContent)) {
            yield $textContent;
        }
    }
}
