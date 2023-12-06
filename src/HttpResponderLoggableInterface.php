<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface HttpResponderLoggableInterface extends HttpResponderInterface
{
    public function shoudLog(): bool;
}
