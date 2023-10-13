<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use League\CommonMark\Util\Xml;
use Stringable;

final readonly class CommonMarkInlineCodeRenderer implements NodeRendererInterface
{
    private const NAMESPACE_REGEXP = '/^([\w|\\\\]+)$/';

    /**
     * @param Code $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): Stringable
    {
        Code::assertInstanceOf($node);

        $escaped = Xml::escape($node->getLiteral());

        return new HtmlElement(
            'code',
            [],
            match (preg_match(self::NAMESPACE_REGEXP, $node->getLiteral())) {
                1 => $this->addWordBreaks($escaped),
                0, false => $escaped,
            },
        );
    }

    private function addWordBreaks(string $namespace): string
    {
        return implode(
            '\\<wbr>',
            explode('\\', $namespace),
        );
    }
}
