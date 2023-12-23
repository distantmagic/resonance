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
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

/**
 * @template-extends SingletonProvider<UrlGenerator>
 */
#[Singleton(provides: UrlGenerator::class)]
final readonly class UrlGeneratorProvider extends SingletonProvider
{
    public function __construct(
        private LoggerInterface $logger,
        private RequestContext $requestContext,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): UrlGenerator
    {
        $routeCollection = new RouteCollection();

        foreach ($phpProjectFiles->findByAttribute(RespondsToHttp::class) as $httpResponderReflection) {
            $attribute = $httpResponderReflection->attribute;

            if (!$attribute->routeSymbol) {
                continue;
            }

            $routeName = $attribute->routeSymbol->toConstant();

            if ($routeCollection->get($routeName)) {
                throw new LogicException('Duplicate route name: '.$routeName);
            }

            $route = new RespondsToHttpAttributeRoute($attribute);

            $routeCollection->add($routeName, $route->symfonyRoute, $attribute->priority);
        }

        return new UrlGenerator(
            $routeCollection,
            $this->requestContext,
            $this->logger,
        );
    }
}
