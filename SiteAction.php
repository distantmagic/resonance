<?php

declare(strict_types=1);

namespace Resonance;

enum SiteAction implements SiteActionInterface
{
    use NameableEnumTrait;

    case StartWebSocketRPCConnection;
}
