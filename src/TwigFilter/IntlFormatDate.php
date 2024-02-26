<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\TwigFilter;

use DateTimeInterface;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\TwigFilter as TwigFilterAttribute;
use Distantmagic\Resonance\IntlFormatter;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\TwigFilter;
use Psr\Http\Message\ServerRequestInterface;

#[Singleton(collection: SingletonCollection::TwigFilter)]
#[TwigFilterAttribute]
readonly class IntlFormatDate extends TwigFilter
{
    public function __construct(private IntlFormatter $intlFormatter) {}

    public function __invoke(null|DateTimeInterface|string $date, ServerRequestInterface $request): string
    {
        return $this->intlFormatter->formatDate($request, $date);
    }

    public function getName(): string
    {
        return 'intl_format_date';
    }
}
