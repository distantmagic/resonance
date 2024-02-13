<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum SingletonCollection implements SingletonCollectionInterface
{
    case AuthenticatedUserStore;
    case CronJob;
    case CrudActionGate;
    case DoctrineEntityListener;
    case DoctrineEventListener;
    case EventListener;
    case GraphQLRootField;
    case GrpcClient;
    case HttpControllerParameterResolver;
    case HttpInterceptor;
    case HttpMiddleware;
    case HttpParameterBinder;
    case HttpResponder;
    case InputValidator;
    case OAuth2Grant;
    case OpenAPIMetadataResponseExtractor;
    case OpenAPIMetadataSecurityRequirementExtractor;
    case OpenAPIRouteParameterExtractor;
    case OpenAPIRouteRequestBodyContentExtractor;
    case OpenAPIRouteSecurityRequirementExtractor;
    case PDOPoolConnectionBuilder;
    case ServerPipeMessageHandler;
    case ServerTaskHandler;
    case SiteActionGate;
    case StaticPageLayout;
    case TickTimerJob;
    case TwigExtension;
    case TwigFilter;
    case TwigFunction;
    case TwigLoader;
    case WebSocketProtocolController;
    case WebSocketRPCResponder;
}
