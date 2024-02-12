<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\OpenAPIMetadataResponseExtractor;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\Can;
use Distantmagic\Resonance\Attribute\ExtractsOpenAPIMetadataResponse;
use Distantmagic\Resonance\Attribute\RespondsWith;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\OpenAPIMetadataResponseExtractor;
use Distantmagic\Resonance\OpenAPISchemaResponse;
use Distantmagic\Resonance\RespondsWithAttributeCollection;
use Distantmagic\Resonance\SingletonCollection;
use ReflectionClass;
use RuntimeException;

/**
 * `Can` attribute has `onForbiddenRespondWith` property, which can generate an
 * additional type of a resonse.
 *
 * @template-extends OpenAPIMetadataResponseExtractor<Can>
 */
#[ExtractsOpenAPIMetadataResponse(Can::class)]
#[Singleton(collection: SingletonCollection::OpenAPIMetadataResponseExtractor)]
readonly class CanExtractor extends OpenAPIMetadataResponseExtractor
{
    public function __construct(
        private RespondsWithAttributeCollection $respondsWithAttributeCollection,
        private RespondsWithExtractor $respondsWithExtractor,
    ) {}

    public function extractFromHttpControllerMetadata(
        ReflectionClass $reflectionClass,
        Attribute $attribute,
    ): array {
        /**
         * @var list<OpenAPISchemaResponse> $ret
         */
        $ret = [];

        if (!$attribute->onForbiddenRespondWith) {
            return $ret;
        }

        $respondsWithAttributes = $this
            ->respondsWithAttributeCollection
            ->attributes
            ->get($attribute->onForbiddenRespondWith, null)
        ;

        if (is_null($respondsWithAttributes)) {
            throw new RuntimeException(sprintf(
                'You need to add at least one "%s" attribute on "%s"',
                RespondsWith::class,
                $attribute->onForbiddenRespondWith,
            ));
        }

        foreach ($respondsWithAttributes as $respondsWithAttribute) {
            $extractedResponses = $this
                ->respondsWithExtractor
                ->extractFromHttpControllerMetadata(
                    $reflectionClass,
                    $respondsWithAttribute->attribute,
                )
            ;

            foreach ($extractedResponses as $extractedResponse) {
                $ret[] = $extractedResponse;
            }
        }

        return $ret;
    }
}
