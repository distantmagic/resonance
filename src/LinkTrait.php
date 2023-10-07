<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

trait LinkTrait
{
    /**
     * @param array<string,scalar> $attributes
     * @param array<int,string>    $rels
     */
    public function __construct(
        private readonly string $href,
        private readonly array $attributes = [],
        private readonly array $rels = [],
    ) {}

    /**
     * @return array<string,scalar>
     */
    public function getAttributes(): array
    {
        /**
         * @var array<string,scalar>
         */
        return $this->attributes;
    }

    public function getHref(): string
    {
        return $this->href;
    }

    /**
     * @return array<int, string>
     */
    public function getRels(): array
    {
        /**
         * @var array<int,string>
         */
        return $this->rels;
    }
}
