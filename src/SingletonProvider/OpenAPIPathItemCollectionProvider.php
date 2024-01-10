<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\BelongsToOpenAPISchema;
use Distantmagic\Resonance\Attribute\RequiresOAuth2Scope;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpResponderCollection;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\OpenAPIPathItem;
use Distantmagic\Resonance\OpenAPIPathItemCollection;
use Distantmagic\Resonance\OpenAPISchemaSymbol;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\ReflectionClassAttributeManager;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use LogicException;
use ReflectionClass;

/**
 * @template-extends SingletonProvider<HttpResponderCollection>
 */
#[Singleton(provides: OpenAPIPathItemCollection::class)]
final readonly class OpenAPIPathItemCollectionProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): OpenAPIPathItemCollection
    {
        $openAPIPathItemCollection = new OpenAPIPathItemCollection();

        foreach ($phpProjectFiles->findByAttribute(BelongsToOpenAPISchema::class) as $belongsToOpenAPISchemaFile) {
            $reflectionClassAttributeManager = new ReflectionClassAttributeManager($belongsToOpenAPISchemaFile->reflectionClass);
            $respondsToHttpAttribute = $reflectionClassAttributeManager->findAttribute(RespondsToHttp::class);

            if (!$respondsToHttpAttribute) {
                throw new LogicException(sprintf(
                    '"%s" attribute also requires "%s" attribute',
                    BelongsToOpenAPISchema::class,
                    RespondsToHttp::class,
                ));
            }

            if (!is_a($belongsToOpenAPISchemaFile->reflectionClass->getName(), HttpResponderInterface::class, true)) {
                throw new LogicException(sprintf(
                    'Only "%s" can be a part of OpenAPI schema',
                    HttpResponderInterface::class,
                ));
            }

            /**
             * @var ReflectionClass<HttpResponderInterface> $httpResponderReflectionClass
             */
            $httpResponderReflectionClass = $belongsToOpenAPISchemaFile->reflectionClass;
            $httpResponderReflectionClassAttributeManager = new ReflectionClassAttributeManager($httpResponderReflectionClass);
            $requiredOAuth2Scopes = $httpResponderReflectionClassAttributeManager->findAttributes(RequiresOAuth2Scope::class);

            $openAPIPathItemCollection->pathItems->add(new OpenAPIPathItem(
                $belongsToOpenAPISchemaFile->attribute->schemaSymbol,
                $httpResponderReflectionClass,
                $requiredOAuth2Scopes,
                $respondsToHttpAttribute,
            ));
            $openAPIPathItemCollection->pathItems->add(new OpenAPIPathItem(
                OpenAPISchemaSymbol::All,
                $httpResponderReflectionClass,
                $requiredOAuth2Scopes,
                $respondsToHttpAttribute,
            ));
        }

        return $openAPIPathItemCollection;
    }
}
