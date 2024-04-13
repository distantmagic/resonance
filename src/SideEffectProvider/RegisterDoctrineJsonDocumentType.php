<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SideEffectProvider;

use Distantmagic\Resonance\Attribute\SideEffect;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\SideEffectProvider;
use Doctrine\DBAL\Types\Type;
use Dunglas\DoctrineJsonOdm\Serializer;
use Dunglas\DoctrineJsonOdm\Type\JsonDocumentType;
use RuntimeException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\UidNormalizer;

#[SideEffect(Feature::Doctrine)]
#[Singleton]
readonly class RegisterDoctrineJsonDocumentType extends SideEffectProvider
{
    public function provideSideEffect(): void
    {
        if (!Type::hasType('json_document')) {
            Type::addType('json_document', JsonDocumentType::class);

            /**
             * @var JsonDocumentType $jsonDocumentType
             */
            $jsonDocumentType = Type::getType('json_document');

            $jsonDocumentType->setSerializer(
                new Serializer([
                    new BackedEnumNormalizer(),
                    new UidNormalizer(),
                    new DateTimeNormalizer(),
                    new ArrayDenormalizer(),
                    new ObjectNormalizer(),
                ], [
                    new JsonEncoder(),
                ])
            );
        } elseif (!(Type::getType('json_document') instanceof JsonDocumentType)) {
            throw new RuntimeException(sprintf(
                'Expected Doctrine to use %s',
                JsonDocumentType::class,
            ));
        }
    }
}
