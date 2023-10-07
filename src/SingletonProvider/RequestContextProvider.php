<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Symfony\Component\Routing\RequestContext;

/**
 * @template-extends SingletonProvider<RequestContext>
 */
#[Singleton(provides: RequestContext::class)]
final readonly class RequestContextProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): RequestContext
    {
        return new RequestContext();
    }
}
