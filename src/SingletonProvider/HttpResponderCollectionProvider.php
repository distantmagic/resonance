<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpControllerDependencies;
use Distantmagic\Resonance\HttpResponder\FunctionResponder;
use Distantmagic\Resonance\HttpResponderCollection;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\HttpResponderWithAttribute;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\ReflectionAttributeManager;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use ReflectionClass;

/**
 * @template-extends SingletonProvider<HttpResponderCollection>
 */
#[RequiresSingletonCollection(SingletonCollection::HttpResponder)]
#[Singleton(provides: HttpResponderCollection::class)]
final readonly class HttpResponderCollectionProvider extends SingletonProvider
{
    public function __construct(
        private HttpControllerDependencies $httpControllerDependencies,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): HttpResponderCollection
    {
        $httpResponderCollection = new HttpResponderCollection();

        $uniqueResponderId = 1;

        foreach ($singletons->values() as $singleton) {
            if (!($singleton instanceof HttpResponderInterface)) {
                continue;
            }

            $reflectionClass = new ReflectionClass($singleton);
            $reflecitonAttributeManager = new ReflectionAttributeManager($reflectionClass);

            $respondsToHttp = $reflecitonAttributeManager->findAttribute(RespondsToHttp::class);

            if (!$respondsToHttp) {
                continue;
            }

            $httpResponderCollection->httpResponders->put(
                (string) $uniqueResponderId,
                new HttpResponderWithAttribute(
                    httpResponder: $singleton,
                    reflection: $reflectionClass,
                    respondsToHttp: $respondsToHttp,
                ),
            );

            ++$uniqueResponderId;
        }

        foreach ($phpProjectFiles->findFunctionByAttribute(RespondsToHttp::class) as $functionResponder) {
            $httpResponderCollection->httpResponders->put(
                (string) $uniqueResponderId,
                new HttpResponderWithAttribute(
                    httpResponder: new FunctionResponder(
                        controllerDependencies: $this->httpControllerDependencies,
                        responderFunctionReflection: $functionResponder->reflectionFunction,
                    ),
                    reflection: $functionResponder->reflectionFunction,
                    respondsToHttp: $functionResponder->attribute,
                ),
            );

            ++$uniqueResponderId;
        }

        return $httpResponderCollection;
    }
}
