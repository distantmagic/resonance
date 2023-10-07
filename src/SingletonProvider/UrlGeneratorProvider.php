<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
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
        private RouteCollection $routecollection,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): UrlGenerator
    {
        return new UrlGenerator(
            $this->routecollection,
            $this->requestContext,
            $this->logger,
        );
    }
}
