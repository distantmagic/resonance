<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpControllerReflectionMethod;
use Distantmagic\Resonance\HttpControllerReflectionMethodCollection;
use Distantmagic\Resonance\HttpResponder\HttpController;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\RespondsToHttpAttributeCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use ReflectionClass;

/**
 * @template-extends SingletonProvider<HttpControllerReflectionMethodCollection>
 */
#[Singleton(provides: HttpControllerReflectionMethodCollection::class)]
final readonly class HttpControllerReflectionMethodCollectionProvider extends SingletonProvider
{
    public function __construct(
        private RespondsToHttpAttributeCollection $respondsToHttpAttributeCollection,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): HttpControllerReflectionMethodCollection
    {
        $httpControllerMetadataCollection = new HttpControllerReflectionMethodCollection();

        foreach ($this->respondsToHttpAttributeCollection->attributes as $httpResponderAttribute) {
            $httpResponderClassName = $httpResponderAttribute->reflectionClass->getName();

            if (is_a($httpResponderClassName, HttpController::class, true)) {
                $reflectionMethod = $httpResponderAttribute
                    ->reflectionClass
                    ->getMethod(HttpController::MAGIC_METHOD_RESPOND)
                ;

                /**
                 * @var ReflectionClass<HttpController> $httpResponderAttribute->reflectionClass
                 */
                $httpControllerMetadataCollection->reflectionMethods->put(
                    $httpResponderClassName,
                    new HttpControllerReflectionMethod(
                        $httpResponderAttribute->reflectionClass,
                        $reflectionMethod,
                    ),
                );
            }
        }

        return $httpControllerMetadataCollection;
    }
}
