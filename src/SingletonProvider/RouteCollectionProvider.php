<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * @template-extends SingletonProvider<RouteCollection>
 */
#[Singleton(provides: RouteCollection::class)]
final readonly class RouteCollectionProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): RouteCollection
    {
        $routeCollection = new RouteCollection();

        foreach ($phpProjectFiles->findByAttribute(RespondsToHttp::class) as $httpResponderReflection) {
            $route = new Route($httpResponderReflection->attribute->pattern);
            $route->setMethods($httpResponderReflection->attribute->method->value);

            if ($httpResponderReflection->attribute->requirements) {
                $route->setRequirements($httpResponderReflection->attribute->requirements);
            }

            $route->compile();

            $routeName = $httpResponderReflection
                ->attribute
                ->routeSymbol
                ->toConstant()
            ;

            $routeCollection->add($routeName, $route);
        }

        return $routeCollection;
    }
}
