<?php

declare(strict_types=1);

namespace Resonance;

use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use League\CommonMark\Util\Xml;
use League\CommonMark\Xml\XmlNodeRendererInterface;
use Stringable;

final class CommonMarkFencedCodeRenderer implements NodeRendererInterface, XmlNodeRendererInterface
{
    /**
     * @param FencedCode $node
     *
     * @return array<string, scalar>
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function getXmlAttributes(Node $node): array
    {
        FencedCode::assertInstanceOf($node);

        if (($info = $node->getInfo()) === null || '' === $info) {
            return [];
        }

        return ['info' => $info];
    }

    public function getXmlTagName(Node $node): string
    {
        return 'code_block';
    }

    /**
     * @param FencedCode $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): Stringable
    {
        FencedCode::assertInstanceOf($node);

        $attrs = $node->data->getData('attributes');

        $infoWords = $node->getInfoWords();
        if (0 !== count($infoWords) && '' !== $infoWords[0]) {
            $attrs->append('class', 'language-'.$infoWords[0]);
            $attrs->append('data-controller', 'hljs');
        }

        /**
         * @var array<string, array<string>> $exportedAttrs
         */
        $exportedAttrs = $attrs->export();

        return new HtmlElement(
            'pre',
            [],
            new HtmlElement('code', $exportedAttrs, Xml::escape($node->getLiteral()))
        );
    }
}
