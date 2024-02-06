<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\OpenAPIRouteParameterExtractor;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\DoctrineEntityRouteParameter;
use Distantmagic\Resonance\Attribute\ExtractsOpenAPIRouteParameter;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Constraint\IntegerConstraint;
use Distantmagic\Resonance\Constraint\StringConstraint;
use Distantmagic\Resonance\DoctrineAttributeDriver;
use Distantmagic\Resonance\DoctrineEntityManagerRepository;
use Distantmagic\Resonance\JsonSchemableInterface;
use Distantmagic\Resonance\OpenAPIParameterIn;
use Distantmagic\Resonance\OpenAPIRouteParameterExtractor;
use Distantmagic\Resonance\OpenAPISchemaParameter;
use Distantmagic\Resonance\SingletonCollection;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use RuntimeException;

/**
 * @template-extends OpenAPIRouteParameterExtractor<DoctrineEntityRouteParameter>
 */
#[ExtractsOpenAPIRouteParameter(DoctrineEntityRouteParameter::class)]
#[Singleton(collection: SingletonCollection::OpenAPIRouteParameterExtractor)]
readonly class DoctrineEntityRouteParameterExtractor extends OpenAPIRouteParameterExtractor
{
    public function __construct(
        private DoctrineAttributeDriver $doctrineAttributeDriver,
        private DoctrineEntityManagerRepository $doctrineEntityManagerRepository,
    ) {}

    /**
     * Unfortunately to extract the metadata factory we need an instance of
     * EntityManager, which in turn starts database connection, just to extract
     * the metadata..
     */
    public function extractFromHttpControllerParameter(
        Attribute $attribute,
        string $parameterClass,
        string $parameterName,
    ): array {
        return $this
            ->doctrineEntityManagerRepository
            ->withEntityManager(function (EntityManagerInterface $entityManager) use ($attribute, $parameterClass): array {
                return $this->extractWithEntityManager($attribute, $entityManager, $parameterClass);
            })
        ;
    }

    /**
     * @param class-string $parameterClass
     *
     * @return list<OpenAPISchemaParameter>
     */
    private function extractWithEntityManager(
        DoctrineEntityRouteParameter $attribute,
        EntityManagerInterface $entityManager,
        string $parameterClass,
    ): array {
        $parameterFieldType = $entityManager
            ->getMetadataFactory()
            ->getMetadataFor($parameterClass)
            ->getTypeOfField($attribute->lookupField)
        ;

        if (is_null($parameterFieldType)) {
            throw new RuntimeException('Unable do determine Doctrine parameter field type');
        }

        $parameter = new OpenAPISchemaParameter(
            in: OpenAPIParameterIn::Path,
            name: $attribute->from,
            required: true,
            jsonSchemable: $this->jsonSchemableFromFieldType($parameterFieldType),
        );

        return [
            $parameter,
        ];
    }

    private function jsonSchemableFromFieldType(string $fieldType): JsonSchemableInterface
    {
        return match ($fieldType) {
            'integer' => new IntegerConstraint(),
            'string' => new StringConstraint(),
            default => throw new LogicException(sprintf(
                'Unsupported Doctrine field type: "%s"',
                $fieldType,
            )),
        };
    }
}
