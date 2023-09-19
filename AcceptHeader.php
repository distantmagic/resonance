<?php

declare(strict_types=1);

namespace Resonance;

use Ds\PriorityQueue;
use Ds\Set;
use Swoole\Http\Request;

final readonly class AcceptHeader
{
    private const QUALITY_PRECISION = 10000;

    /**
     * @var Set<string>
     */
    public Set $sorted;

    public static function fromRequest(Request $request, string $name, string $default): self
    {
        /**
         * @var string $header
         */
        $header = isset($request->header[$name]) && is_string($request->header[$name])
            ? $request->header[$name]
            : $default;

        return new self($header);
    }

    public static function mime(Request $request): self
    {
        return self::fromRequest($request, 'accept', '*/*');
    }

    public function __construct(string $header)
    {
        /**
         * @var Set<string>
         */
        $this->sorted = new Set();

        /**
         * @var PriorityQueue<string> $sorter
         */
        $sorter = new PriorityQueue();

        foreach (explode(',', $header) as $entry) {
            $chunks = explode(';', $entry);

            if (isset($chunks[0])) {
                $quality = isset($chunks[1])
                    ? (int) ($this->parseQuality($chunks[1]) * self::QUALITY_PRECISION)
                    : self::QUALITY_PRECISION;

                if ($quality > 0) {
                    $sorter->push(trim($chunks[0]), $quality);
                }
            }
        }

        foreach ($sorter as $entry) {
            $this->sorted->add($entry);
        }
    }

    public function getQuality(string $entry): ?int
    {
        $total = $this->sorted->count();

        foreach ($this->sorted as $index => $sortedEntry) {
            if (fnmatch($sortedEntry, $entry)) {
                return $total - $index;
            }
        }

        return 0;
    }

    private function parseQuality(string $qualityDirective): float
    {
        if (!str_contains($qualityDirective, 'q=')) {
            return 1.0;
        }

        [,$quality] = explode('q=', $qualityDirective);

        return (float) $quality;
    }
}
