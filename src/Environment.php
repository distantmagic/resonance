<?php

declare(strict_types=1);

namespace Resonance;

enum Environment: string
{
    use EnumValuesTrait;

    case Development = 'development';
    case Production = 'production';
}
