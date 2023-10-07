<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum StaticPageLayoutHandler: string
{
    use EnumValuesTrait;

    case Document = 'document';
    case Page = 'page';
}
