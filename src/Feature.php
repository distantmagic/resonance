<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum Feature implements FeatureInterface
{
    use NameableEnumTrait;

    case Doctrine;
    case Grpc;
    case HttpSession;
    case Mailer;
    case OAuth2;
    case Postfix;
    case StaticPages;
    case SwooleTaskServer;
    case WebSocket;
}
