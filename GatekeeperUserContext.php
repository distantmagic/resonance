<?php

declare(strict_types=1);

namespace Resonance;

use App\CrudActionGate\BlogPostGate;
use App\CrudActionGate\WorldMapGate;
use App\DatabaseEntity\BlogPostInterface;
use App\DatabaseEntity\WorldMapInterface;
use App\DatabaseEntity\WorldMapTile;
use DomainException;

readonly class GatekeeperUserContext
{
    public function __construct(
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

    public function crud(object $subject): CrudActionGate
    {
        return match (true) {
            $subject instanceof BlogPostInterface => new BlogPostGate($subject, $this->user),
            $subject instanceof WorldMapInterface => new WorldMapGate($this->user),
            $subject instanceof WorldMapTile => new WorldMapGate($this->user),
            default => throw new DomainException('Unsupported gatekeeper crud gate:'.$subject::class),
        };
    }
}
