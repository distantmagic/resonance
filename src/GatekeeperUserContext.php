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

    /**
     * @param iterable<CrudActionSubjectInterface> $subjects
     */
    public function canCrudAll(iterable $subjects, CrudAction $crudAction): bool
    {
        foreach ($subjects as $subject) {
            if (!$this->canCrud($subject, $crudAction)) {
                return false;
            }
        }

        return true;
    }

    public function canDelete(CrudActionSubjectInterface $subject): bool
    {
        return $this->canCrud($subject, CrudAction::Delete);
    }

    /**
     * @param iterable<CrudActionSubjectInterface> $subjects
     */
    public function canDeleteAll(iterable $subjects): bool
    {
        return $this->canCrudAll($subjects, CrudAction::Delete);
    }

    public function canRead(CrudActionSubjectInterface $subject): bool
    {
        return $this->canCrud($subject, CrudAction::Read);
    }

    /**
     * @param iterable<CrudActionSubjectInterface> $subjects
     */
    public function canReadAll(iterable $subjects): bool
    {
        return $this->canCrudAll($subjects, CrudAction::Read);
    }

    public function canUpdate(CrudActionSubjectInterface $subject): bool
    {
        return $this->canCrud($subject, CrudAction::Update);
    }

    /**
     * @param iterable<CrudActionSubjectInterface> $subjects
     */
    public function canUpdateAll(iterable $subjects): bool
    {
        return $this->canCrudAll($subjects, CrudAction::Update);
    }
}
