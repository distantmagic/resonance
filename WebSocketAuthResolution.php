<?php

declare(strict_types=1);

namespace Resonance;

/**
 * Remove this suppresion after User is used somewhere (and it cetainly is
 * going to be used somewhere).
 *
 * @psalm-suppress PossiblyUnusedProperty
 */
readonly class WebSocketAuthResolution
{
    public function __construct(
        public bool $isAuthorizedToConnect,
        public ?UserInterface $user = null,
    ) {}
}
