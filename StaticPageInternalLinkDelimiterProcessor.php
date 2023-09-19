<?php

declare(strict_types=1);

namespace Resonance;

use Generator;
use League\CommonMark\Delimiter\DelimiterInterface;
use League\CommonMark\Delimiter\Processor\DelimiterProcessorInterface;
use League\CommonMark\Node\Inline\AbstractStringContainer;
use League\CommonMark\Node\Node;

readonly class StaticPageInternalLinkDelimiterProcessor implements DelimiterProcessorInterface
{
    public function getClosingCharacter(): string
    {
        return '}';
    }

    public function getDelimiterUse(DelimiterInterface $opener, DelimiterInterface $closer): int
    {
        if ($opener->getLength() !== $closer->getLength()) {
            return 0;
        }

        if (2 !== $opener->getLength()) {
            return 0;
        }

        return 2;
    }

    public function getMinLength(): int
    {
        return 2;
    }

    public function getOpeningCharacter(): string
    {
        return '{';
    }

    public function process(
        AbstractStringContainer $opener,
        AbstractStringContainer $closer,
        int $delimiterUse,
    ): void {
        $internalLinkNode = new StaticPageInternalLinkNode();

        foreach ($this->childNodes($opener, $closer) as $childNode) {
            $internalLinkNode->appendChild($childNode);
        }

        $opener->insertAfter($internalLinkNode);
    }

    /**
     * @return Generator<Node>
     */
    private function childNodes(AbstractStringContainer $opener, AbstractStringContainer $closer): Generator
    {
        $tmp = $opener->next();

        while (null !== $tmp && $tmp !== $closer) {
            $next = $tmp->next();
            yield $tmp;
            $tmp = $next;
        }
    }
}
