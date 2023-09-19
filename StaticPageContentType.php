<?php

declare(strict_types=1);

namespace Resonance;

enum StaticPageContentType: string
{
    use EnumValuesTrait;

    case Html = 'html';
    case Markdown = 'markdown';
}
