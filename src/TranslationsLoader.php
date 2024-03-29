<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use RuntimeException;

readonly class TranslationsLoader
{
    /**
     * @psalm-taint-sink file $filename
     *
     * @return Map<string, string>
     */
    public static function load(string $filename): Map
    {
        return (new self($filename))->getTranslations();
    }

    /**
     * @psalm-taint-sink file $filename
     */
    public function __construct(private string $filename) {}

    /**
     * @return Map<string, string>
     */
    public function getTranslations(): Map
    {
        /**
         * @var array<string, array<string>>|false
         */
        $translations = parse_ini_file(
            filename: $this->filename,
            process_sections: true,
        );

        if (!is_array($translations)) {
            throw new RuntimeException('Unable to parse translations file:'.$this->filename);
        }

        $basename = pathinfo($this->filename, PATHINFO_FILENAME);

        /**
         * @var Map<string, string>
         */
        return ArrayFlattenIterator::flatten([
            $basename => $translations,
        ]);
    }
}
