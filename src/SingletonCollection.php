<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum SingletonCollection implements SingletonCollectionInterface
{
    case CronJob;
    case CrudActionGate;
    case EventListener;
    case GraphQLRootField;
    case HttpControllerParameterResolver;
    case HttpInterceptor;
    case HttpMiddleware;
    case HttpParameterBinder;
    case HttpResponder;
    case InputValidator;
    case OAuth2Grant;
    case OpenAPIRouteParameterExtractor;
    case SiteActionGate;
    case StaticPageLayout;
    case TickTimerJob;
    case TwigExtension;
    case TwigLoader;
    case WebSocketProtocolController;
    case WebSocketRPCResponder;
}
