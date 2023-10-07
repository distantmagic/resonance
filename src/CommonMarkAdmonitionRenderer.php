<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use LogicException;
use Stringable;

final class CommonMarkAdmonitionRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): Stringable
    {
        if (!($node instanceof CommonMarkAdmonitionBlock)) {
            throw new LogicException('Expected node be an instance of '.CommonMarkAdmonitionBlock::class);
        }

        $attrs = $node->data->getData('attributes');
        $attrs->append('class', 'admonition');

        if (!empty($node->fenceType)) {
            $attrs->append('class', 'admonition--'.htmlspecialchars($node->fenceType));
        }

        /**
         * @var array<string,string> $attributes
         */
        $attributes = $attrs->export();

        return new HtmlElement(
            'div',
            $attributes,
            $childRenderer->renderNodes($node->children()),
        );
    }
}
