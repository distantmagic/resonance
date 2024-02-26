<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpControllerParameterResolver;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\DoctrineEntityRepository;
use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\ResolvesHttpControllerParameter;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\DoctrineEntityManagerRepository;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\HttpControllerParameter;
use Distantmagic\Resonance\HttpControllerParameterResolution;
use Distantmagic\Resonance\HttpControllerParameterResolutionStatus;
use Distantmagic\Resonance\HttpControllerParameterResolver;
use Distantmagic\Resonance\SingletonCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @template-extends HttpControllerParameterResolver<DoctrineEntityRepository>
 */
#[GrantsFeature(Feature::Doctrine)]
#[ResolvesHttpControllerParameter(DoctrineEntityRepository::class)]
#[Singleton(collection: SingletonCollection::HttpControllerParameterResolver)]
readonly class DoctrineEntityRepositoryResolver extends HttpControllerParameterResolver
{
    public function __construct(
        private DoctrineEntityManagerRepository $doctrineEntityManagerRepository,
    ) {}

    public function resolve(
        ServerRequestInterface $request,
        ResponseInterface $response,
        HttpControllerParameter $parameter,
        Attribute $attribute,
    ): HttpControllerParameterResolution {
        $entityManager = $this
            ->doctrineEntityManagerRepository
            ->getEntityManager($request, $attribute->connection)
        ;

        return new HttpControllerParameterResolution(
            HttpControllerParameterResolutionStatus::Success,
            $entityManager->getRepository($attribute->entityClass),
        );
    }
}
