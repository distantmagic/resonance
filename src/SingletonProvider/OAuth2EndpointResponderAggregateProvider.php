<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\RespondsToOAuth2Endpoint;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\HttpResponderCollection;
use Distantmagic\Resonance\OAuth2Endpoint;
use Distantmagic\Resonance\OAuth2EndpointResponderAggregate;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\ReflectionClassAttributeManager;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use LogicException;
use RuntimeException;

/**
 * @template-extends SingletonProvider<HttpResponderCollection>
 */
#[GrantsFeature(Feature::OAuth2)]
#[Singleton(provides: OAuth2EndpointResponderAggregate::class)]
final readonly class OAuth2EndpointResponderAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): OAuth2EndpointResponderAggregate
    {
        $oAuth2EndpointResponderAggregate = new OAuth2EndpointResponderAggregate();

        foreach ($phpProjectFiles->findByAttribute(RespondsToOAuth2Endpoint::class) as $oAuth2EndpointResponderFile) {
            $reflectionClassAttributeManager = new ReflectionClassAttributeManager($oAuth2EndpointResponderFile->reflectionClass);
            $respondsToHttpAttribute = $reflectionClassAttributeManager->findAttribute(RespondsToHttp::class);

            if (!$respondsToHttpAttribute) {
                throw new LogicException(sprintf(
                    '"%s" attribute also requires "%s" attribute',
                    RespondsToOAuth2Endpoint::class,
                    RespondsToHttp::class,
                ));
            }

            $routeSymbol = $respondsToHttpAttribute->routeSymbol;

            if (!$routeSymbol) {
                throw new LogicException(sprintf(
                    'Http responder requires a route symbol: %s',
                    $oAuth2EndpointResponderFile->reflectionClass->getName(),
                ));
            }

            $oAuth2EndpointResponderAggregate->registerEndpoint(
                $oAuth2EndpointResponderFile->attribute->endpoint,
                $routeSymbol,
            );
        }

        $registeredEndpoints = $oAuth2EndpointResponderAggregate->getRegisteredEndpoints();
        $allEndpoints = OAuth2Endpoint::cases();

        if (count($registeredEndpoints) !== count($allEndpoints)) {
            $missingEndpoints = [];

            foreach ($allEndpoints as $endpoint) {
                if (!$registeredEndpoints->contains($endpoint)) {
                    $missingEndpoints[] = $endpoint->name;
                }
            }

            throw new RuntimeException(sprintf(
                'Some OAuth2 endpoints are not registered: "%s"',
                implode('", "', $missingEndpoints),
            ));
        }

        return $oAuth2EndpointResponderAggregate;
    }
}
