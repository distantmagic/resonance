<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

trait EnumValuesTrait
{
    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
