<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use LogicException;

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
        if (!$this->crudActionGates->hasKey($subject::class)) {
            throw new LogicException('There is no CRUD gate assigned to handle subject: '.$subject::class);
        }

        return $this->crudActionGates->get($subject::class);
    }
}
