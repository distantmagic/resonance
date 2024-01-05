<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\BelongsToOpenAPICollection;
use Distantmagic\Resonance\Attribute\RequiresOAuth2Scope;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\OpenAPICollectionAggregate;
use Distantmagic\Resonance\OpenAPICollectionEndpoint;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Ds\Set;
use LogicException;
use ReflectionClass;
use RuntimeException;

/**
 * @template-extends SingletonProvider<OpenAPICollectionAggregate>
 */
#[Singleton(
    grantsFeature: Feature::OAuth2,
    provides: OpenAPICollectionAggregate::class,
)]
final readonly class OpenAPICollectionAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): OpenAPICollectionAggregate
    {
        $openAPICollectionAggregate = new OpenAPICollectionAggregate();

        foreach ($phpProjectFiles->findByAttribute(BelongsToOpenAPICollection::class) as $openAPICollectionMemberAttribute) {
            $httpResponderReflectionClass = $openAPICollectionMemberAttribute->reflectionClass;

            if (!is_a($httpResponderReflectionClass->getName(), HttpResponderInterface::class, true)) {
                throw new RuntimeException(sprintf(
                    'Class tagged with %s attribute must also implement %s interface',
                    BelongsToOpenAPICollection::class,
                    RespondsToHttp::class,
                ));
            }

            $respondsToHttpAttributes = $httpResponderReflectionClass->getAttributes(RespondsToHttp::class);

            if (1 !== count($respondsToHttpAttributes)) {
                throw new LogicException(sprintf(
                    'Classes tagged with %s attribute must also have %s attribute',
                    BelongsToOpenAPICollection::class,
                    RespondsToHttp::class,
                ));
            }

            /**
             * @var Set<RequiresOAuth2Scope>
             */
            $requiredOAuth2Scopes = new Set();

            foreach ($httpResponderReflectionClass->getAttributes(RequiresOAuth2Scope::class) as $requiresOAuth2ScopeReflectionAttribute) {
                $requiredOAuth2Scopes->add($requiresOAuth2ScopeReflectionAttribute->newInstance());
            }

            $respondsToHttpAttribute = current($respondsToHttpAttributes);

            /**
             * @var ReflectionClass<HttpResponderInterface> $httpResponderReflectionClass
             */
            $openAPICollectionEndpoint = new OpenAPICollectionEndpoint(
                $httpResponderReflectionClass,
                $openAPICollectionMemberAttribute->attribute,
                $requiredOAuth2Scopes,
                $respondsToHttpAttribute->newInstance(),
            );
            $openAPICollectionAggregate->registerCollectionEndpoint($openAPICollectionEndpoint);
        }

        return $openAPICollectionAggregate;
    }
}
