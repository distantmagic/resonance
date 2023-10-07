<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use Generator;
use IteratorAggregate;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

/**
 * @template-implements IteratorAggregate<string, mixed>
 */
readonly class ArrayFlattenIterator implements IteratorAggregate
{
    /**
     * @return Map<string, mixed>
     */
    public static function flatten(array $input): Map
    {
        /**
         * @var Map<string, mixed> $ret
         */
        $ret = new Map();

        /**
         * @var mixed $value explicitly mixed for typechecks
         */
        foreach (new self($input) as $key => $value) {
            $ret->put($key, $value);
        }

        return $ret;
    }

    public function __construct(private array $input) {}

    /**
     * @return Generator<string, mixed>
     */
    public function getIterator(): Generator
    {
        $iter = new RecursiveIteratorIterator(new RecursiveArrayIterator($this->input));

        /**
         * @var mixed $leafValue
         */
        foreach ($iter as $leafValue) {
            /**
             * @var array<string> $keys
             */
            $keys = [];

            for ($depth = 0; $depth <= $iter->getDepth(); ++$depth) {
                $subIterator = $iter->getSubIterator($depth);

                if ($subIterator) {
                    $keys[] = (string) $subIterator->key();
                }
            }

            yield implode('.', $keys) => $leafValue;
        }
    }
}
