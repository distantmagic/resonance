<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum SingletonCollection implements SingletonCollectionInterface
{
    case CrudActionGate;
    case EventListener;
    case GraphQLRootQueryField;
    case HttpParameterBinder;
    case HttpResponder;
    case SiteActionGate;
    case WebSocketProtocolController;
    case WebSocketRPCResponder;
}
