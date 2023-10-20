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
    /**
     * @var array<string,string>
     */
    private array $patterns;

    public function __construct()
    {
        $this->patterns = [
            '(' => '(<wbr>',
            '-' => '-<wbr>',
            '->' => '-><wbr>',
            '::' => '::<wbr>',
            '\\' => '\\<wbr>',
        ];
    }

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
            $this->addWordBreaks($escaped),
        );
    }

    private function addWordBreaks(string $code): string
    {
        foreach ($this->patterns as $pattern => $replacement) {
            $code = str_replace($pattern, $replacement, $code);
        }

        return $code;
    }
}
