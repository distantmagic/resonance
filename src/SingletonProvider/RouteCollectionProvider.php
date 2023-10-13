<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use LogicException;
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
            $attribute = $httpResponderReflection->attribute;

            $routeName = $attribute->routeSymbol->toConstant();

            if ($routeCollection->get($routeName)) {
                throw new LogicException('Duplicate route name: '.$routeName);
            }

            $route = new Route($attribute->pattern);
            $route->setMethods($attribute->method->value);

            if ($attribute->requirements) {
                $route->setRequirements($attribute->requirements);
            }

            $route->compile();

            $routeCollection->add($routeName, $route, $attribute->priority);
        }

        return $routeCollection;
    }
}
