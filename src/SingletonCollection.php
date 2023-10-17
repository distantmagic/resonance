<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum SingletonCollection implements SingletonCollectionInterface
{
    case CrudActionGate;
    case EventListener;
    case GraphQLRootField;
    case HttpControllerParameterResolver;
    case HttpMiddleware;
    case HttpParameterBinder;
    case HttpResponder;
    case InputValidator;
    case SiteActionGate;
    case WebSocketProtocolController;
    case WebSocketRPCResponder;
}
