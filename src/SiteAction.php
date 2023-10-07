<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum SiteAction implements SiteActionInterface
{
    use NameableEnumTrait;

    case StartWebSocketRPCConnection;
    case UseGraphQL;
}
