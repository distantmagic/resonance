<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\RespondsWith;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\TestableHttpResponse;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\ReflectionClassAttributeManager;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Distantmagic\Resonance\TestableHttpResponseCollection;
use LogicException;
use ReflectionClass;

/**
 * @template-extends SingletonProvider<TestableHttpResponseCollection>
 */
#[RequiresSingletonCollection(SingletonCollection::HttpResponder)]
#[Singleton(provides: TestableHttpResponseCollection::class)]
final readonly class TestableHttpResponseCollectionProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): TestableHttpResponseCollection
    {
        $testableHttpResponseCollection = new TestableHttpResponseCollection();

        foreach ($this->collectResponders($singletons) as $testableHttpResponseAttribute) {
            $reflectionClass = new ReflectionClass($testableHttpResponseAttribute->singleton);
            $reflectionClassAttributeManager = new ReflectionClassAttributeManager($reflectionClass);

            $respondsWithAttributes = $reflectionClassAttributeManager->findAttributes(RespondsWith::class);

            if ($respondsWithAttributes->isEmpty()) {
                throw new LogicException(sprintf(
                    'To test "%s" it needs to also have "%s" attribute',
                    $testableHttpResponseAttribute->singleton::class,
                    RespondsWith::class,
                ));
            }

            foreach ($respondsWithAttributes as $respondsWithAttribute) {
                $testableHttpResponseCollection->registerTestableHttpResponse(
                    $testableHttpResponseAttribute->singleton,
                    $testableHttpResponseAttribute->attribute,
                    $respondsWithAttribute,
                );
            }
        }

        return $testableHttpResponseCollection;
    }

    /**
     * @return iterable<SingletonAttribute<HttpResponderInterface,TestableHttpResponse>>
     */
    private function collectResponders(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            HttpResponderInterface::class,
            TestableHttpResponse::class,
        );
    }
}
