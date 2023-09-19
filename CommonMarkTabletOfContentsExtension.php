<?php

declare(strict_types=1);

namespace Resonance;

use Generator;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentRenderedEvent;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalink;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\NodeIterator;
use League\CommonMark\Node\RawMarkupContainerInterface;
use League\CommonMark\Node\StringContainerHelper;

readonly class CommonMarkTabletOfContentsExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addEventListener(DocumentRenderedEvent::class, $this->onDocumentRendered(...), -500);
    }

    /**
     * @return Generator<HeadingPermalink>
     */
    private function getHeadingLinks(Heading $heading): Generator
    {
        foreach ($heading->children() as $child) {
            if ($child instanceof HeadingPermalink) {
                yield $child;
            }
        }
    }

    /**
     * @return Generator<Heading>
     */
    private function getHeadings(Document $document): Generator
    {
        foreach ($document->iterator(NodeIterator::FLAG_BLOCKS_ONLY) as $node) {
            if ($node instanceof Heading) {
                yield $node;
            }
        }
    }

    private function getHeadingText(Heading $heading): string
    {
        return StringContainerHelper::getChildText($heading, [
            RawMarkupContainerInterface::class,
        ]);
    }

    /**
     * @return Generator<CommonMarkTableOfContentsLink>
     */
    private function getTableOfContentsLinks(Document $document): Generator
    {
        foreach ($this->getHeadings($document) as $heading) {
            $headingLevel = $heading->getLevel();
            $headingText = $this->getHeadingText($heading);

            foreach ($this->getHeadingLinks($heading) as $headingPermalink) {
                yield new CommonMarkTableOfContentsLink(
                    level: $headingLevel,
                    slug: 'content-'.$headingPermalink->getSlug(),
                    text: $headingText,
                );
            }
        }
    }

    private function onDocumentRendered(DocumentRenderedEvent $event): void
    {
        $output = $event->getOutput();
        $document = $output->getDocument();

        $tableOfContentsLinks = iterator_to_array($this->getTableOfContentsLinks($document));

        if (empty($tableOfContentsLinks)) {
            return;
        }

        $event->replaceOutput(new CommonMarkRenderedContentWithTableOfContentsLinks(
            $document,
            $output->getContent(),
            $tableOfContentsLinks
        ));
    }
}
