<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
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
        private RouteCollection $routeCollection,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): UrlMatcher
    {
        return new UrlMatcher($this->routeCollection, $this->requestContext);
    }
}
