<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Set;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use League\CommonMark\Util\Xml;
use League\CommonMark\Xml\XmlNodeRendererInterface;
use Stringable;

final readonly class CommonMarkFencedCodeRenderer implements NodeRendererInterface, XmlNodeRendererInterface
{
    private const FILE_MODIFIER_PREFIX = 'file:';

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

        return [
            'info' => $info,
        ];
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

        $infoWords = new Set(array_filter($node->getInfoWords()));

        $languageName = $infoWords->isEmpty() ? 'unknown' : $infoWords->first();

        if ($infoWords->contains('render')) {
            return new HtmlElement(
                'div',
                [
                    'class' => 'fenced-renderable',
                    'data-controller' => $languageName,
                ],
                new HtmlElement(
                    'div',
                    [
                        'class' => 'fenced-renderable__scene',
                        "data-{$languageName}-target" => 'scene',
                    ],
                    Xml::escape($node->getLiteral()),
                ),
            );
        }

        $tags = [];

        if ($infoWords->count() > 1) {
            foreach ($infoWords as $modifier) {
                if (str_starts_with($modifier, self::FILE_MODIFIER_PREFIX)) {
                    $filename = substr($modifier, strlen(self::FILE_MODIFIER_PREFIX));

                    array_push($tags, new HtmlElement(
                        'div',
                        [
                            'class' => 'fenced-code__filename',
                        ],
                        $filename,
                    ));
                }
            }
        }

        if (!$infoWords->isEmpty()) {
            array_push($tags, new HtmlElement(
                'div',
                [
                    'class' => 'fenced-code__language-name',
                ],
                $languageName,
            ));
        }

        $codeAttrs = $node->data->getData('attributes');
        $codeAttrs->append('class', 'language-'.$languageName);
        $codeAttrs->append('data-controller', 'hljs');
        $codeAttrs->append('data-hljs-language-value', $languageName);

        /**
         * @var array<string, array<string>> $codeAttrs->export()
         */
        array_push($tags, new HtmlElement(
            'code',
            $codeAttrs->export(),
            Xml::escape($node->getLiteral()),
        ));

        return new HtmlElement(
            'pre',
            [
                'class' => 'fenced-code',
            ],
            $tags,
        );
    }
}
