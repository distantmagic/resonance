<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;

readonly class CrudActionGateAggregate
{
    /**
     * @var Map<class-string<CrudActionSubjectInterface>,CrudActionGateInterface>
     */
    public Map $crudActionGates;

    public function __construct()
    {
        $this->crudActionGates = new Map();
    }

    public function getSubjectGate(CrudActionSubjectInterface $subject): CrudActionGateInterface
    {
        return $this->crudActionGates->get($subject::class);
    }
}
