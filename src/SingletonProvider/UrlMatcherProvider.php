<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\RespondsToHttpAttributeRoute;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use LogicException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

/**
 * @template-extends SingletonProvider<UrlMatcher>
 */
#[Singleton(provides: UrlMatcher::class)]
final readonly class UrlMatcherProvider extends SingletonProvider
{
    public function __construct(
        private RequestContext $requestContext,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): UrlMatcher
    {
        $routeCollection = new RouteCollection();

        foreach ($phpProjectFiles->findByAttribute(RespondsToHttp::class) as $httpResponderReflection) {
            $attribute = $httpResponderReflection->attribute;
            $routeName = $httpResponderReflection->reflectionClass->getName();

            if ($routeCollection->get($routeName)) {
                throw new LogicException('Duplicate route name: '.$routeName);
            }

            $route = new RespondsToHttpAttributeRoute($attribute);

            $routeCollection->add($routeName, $route->symfonyRoute, $attribute->priority);
        }

        return new UrlMatcher($routeCollection, $this->requestContext);
    }
}
