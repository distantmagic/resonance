<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use IntlDateFormatter;

/**
 * Creating a new IntlDateFormatter instance takes a relatively long time.
 * It pays off to cache instances in memory.
 */
readonly class IntlDateFormatterRepository
{
    /**
     * @var Map<string, IntlDateFormatter>
     */
    private Map $formatters;

    public function __construct()
    {
        $this->formatters = new Map();
    }

    public function getFormatter(
        SupportedLanguageCodeInterface $language,
        int $dateTimeFormat,
        int $timeTypeFormat,
    ): IntlDateFormatter {
        $hash = $this->createFormatterHash($language, $dateTimeFormat, $timeTypeFormat);

        if ($this->formatters->hasKey($hash)) {
            return $this->formatters->get($hash);
        }

        $formatter = new IntlDateFormatter(
            $language->getName(),
            $dateTimeFormat,
            $timeTypeFormat,
        );

        $this->formatters->put($hash, $formatter);

        return $formatter;
    }

    private function createFormatterHash(
        SupportedLanguageCodeInterface $language,
        int $dateTimeFormat,
        int $timeTypeFormat,
    ): string {
        return $language->getName().(string) $dateTimeFormat.(string) $timeTypeFormat;
    }
}
