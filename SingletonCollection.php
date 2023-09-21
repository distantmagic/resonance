<?php

declare(strict_types=1);

namespace Resonance;

enum SingletonCollection implements SingletonCollectionInterface
{
    case GraphQLRootQueryField;
    case HttpParameterBinder;
    case HttpResponder;
    case SiteAction;
    case WebSocketProtocolController;
    case WebSocketRPCResponder;
}
