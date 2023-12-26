<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class GatekeeperUserContext
{
    public function __construct(
        private CrudActionGateAggregate $crudActionGateAggregate,
        private SiteActionGateAggregate $siteActionGateAggregate,
        private ?AuthenticatedUser $authenticatedUser,
    ) {}

    public function can(SiteActionInterface $action): bool
    {
        return $this
            ->siteActionGateAggregate
            ->selectSiteActionGate($action)
            ->can($this->authenticatedUser)
        ;
    }

    public function canCrud(CrudActionSubjectInterface $subject, CrudAction $crudAction): bool
    {
        return $this
            ->crudActionGateAggregate
            ->getSubjectGate($subject)
            ->can($this->authenticatedUser, $subject, $crudAction)
        ;
    }
}
