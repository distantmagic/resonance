<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum SiteAction implements SiteActionInterface
{
    use NameableEnumTrait;

    case StartWebSocketJsonRPCConnection;
    case UseGraphQL;
    case UseOAuth2;
}
