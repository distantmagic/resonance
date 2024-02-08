<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum Feature implements FeatureInterface
{
    case Doctrine;
    case HttpSession;
    case OAuth2;
    case Postfix;
    case StaticPages;
    case SwooleTaskServer;
    case WebSocket;

    public function getName(): string
    {
        return $this->name;
    }
}
