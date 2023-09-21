<?php

declare(strict_types=1);

namespace Resonance;

enum SingletonCollection implements SingletonCollectionInterface
{
    case CrudActionGate;
    case GraphQLRootQueryField;
    case HttpParameterBinder;
    case HttpResponder;
    case SiteActionGate;
    case WebSocketProtocolController;
    case WebSocketRPCResponder;
}
