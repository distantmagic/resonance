<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;

readonly class ConstraintResult
{
    /**
     * @param array<ConstraintResult> $nested
     */
    public function __construct(
        public mixed $castedData,
        public ConstraintPath $path,
        public ConstraintResultStatus $status,
        public ConstraintReason $reason,
        public array $nested = [],
        public ?string $comment = null,
    ) {}

    /**
     * @param Map<string,non-empty-string> $errors
     *
     * @return Map<string,non-empty-string>
     */
    public function getErrors(Map $errors = new Map()): Map
    {
        if ($this->status->isValid()) {
            return $errors;
        }

        $errors->put((string) $this->path, $this->reason->value);

        foreach ($this->nested as $nestedResult) {
            $nestedResult->getErrors($errors);
        }

        return $errors;
    }
}
