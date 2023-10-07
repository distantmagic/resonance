<?php

declare(strict_types=1);

namespace Resonance;

readonly class GatekeeperUserContext
{
    public function __construct(
        private CrudActionGateAggregate $crudActionGateAggregate,
        private SiteActionGateAggregate $siteActionGateAggregate,
        private ?UserInterface $user,
    ) {}

    public function can(SiteActionInterface $action): bool
    {
        return $this
            ->siteActionGateAggregate
            ->selectSiteActionGate($action)
            ->can($this->user)
        ;
    }

    public function canCrud(CrudActionSubjectInterface $subject, CrudAction $crudAction): bool
    {
        return $this
            ->crudActionGateAggregate
            ->getSubjectGate($subject)
            ->can($this->user, $subject, $crudAction)
        ;
    }
}
