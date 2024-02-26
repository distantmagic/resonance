<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use DateTimeImmutable;
use DateTimeInterface;
use Distantmagic\Resonance\Attribute\RequiresPhpExtension;
use Distantmagic\Resonance\Attribute\Singleton;
use IntlDateFormatter;
use Psr\Http\Message\ServerRequestInterface;

#[Singleton]
#[RequiresPhpExtension('intl')]
final readonly class IntlFormatter
{
    private IntlDateFormatterRepository $formatters;

    public function __construct(private HttpRequestLanguageDetector $httpRequestLanguageDetector)
    {
        $this->formatters = new IntlDateFormatterRepository();
    }

    public function formatDate(
        ServerRequestInterface $request,
        null|DateTimeInterface|string $date,
        int $dateTimeFormat = IntlDateFormatter::MEDIUM,
        int $timeTypeFormat = IntlDateFormatter::SHORT,
    ): string {
        if (is_null($date)) {
            return '';
        }

        $language = $this->httpRequestLanguageDetector->detectPrimaryLanguage($request);
        $dateTime = is_string($date) ? new DateTimeImmutable($date) : $date;

        return $this
            ->formatters
            ->getFormatter($language, $dateTimeFormat, $timeTypeFormat)
            ->format($dateTime)
        ;
    }
}
