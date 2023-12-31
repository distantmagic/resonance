<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use DateTimeInterface;
use Distantmagic\Resonance\Attribute\Singleton;
use Swoole\Http\Request;

#[Singleton]
readonly class TwigFilterIntlFormatDate
{
    public function __construct(private IntlFormatter $intlFormatter) {}

    public function __invoke(null|DateTimeInterface|string $date, Request $request): string
    {
        return $this->intlFormatter->formatDate($request, $date);
    }
}
