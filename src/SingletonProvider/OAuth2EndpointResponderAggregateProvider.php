<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\RespondsToOAuth2Endpoint;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\HttpResponderCollection;
use Distantmagic\Resonance\OAuth2EndpointResponderAggregate;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use LogicException;
use ReflectionAttribute;

/**
 * @template-extends SingletonProvider<HttpResponderCollection>
 */
#[Singleton(
    grantsFeature: Feature::OAuth2,
    provides: OAuth2EndpointResponderAggregate::class,
)]
final readonly class OAuth2EndpointResponderAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): OAuth2EndpointResponderAggregate
    {
        $oAuth2EndpointResponderAggregate = new OAuth2EndpointResponderAggregate();

        foreach ($phpProjectFiles->findByAttribute(RespondsToOAuth2Endpoint::class) as $oAuth2EndpointResponderFile) {
            $respondsToHttpAttributes = $oAuth2EndpointResponderFile->reflectionClass->getAttributes(
                RespondsToHttp::class,
                ReflectionAttribute::IS_INSTANCEOF,
            );

            if (1 !== count($respondsToHttpAttributes) || !isset($respondsToHttpAttributes[0])) {
                throw new LogicException('Responds to OAuth2Endpoint requres Responds to HTTP attribute');
            }

            $respondsToHttpAttribute = $respondsToHttpAttributes[0];
            $routeSymbol = $respondsToHttpAttribute->newInstance()->routeSymbol;

            if (!$routeSymbol) {
                throw new LogicException(sprintf(
                    'Http responder requires a route symbol: %s',
                    $oAuth2EndpointResponderFile->reflectionClass->getName(),
                ));
            }

            $oAuth2EndpointResponderAggregate->endpointResponderRouteSymbol->put(
                $oAuth2EndpointResponderFile->attribute->endpoint,
                $routeSymbol,
            );
        }

        return $oAuth2EndpointResponderAggregate;
    }
}
