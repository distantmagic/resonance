<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpControllerParameterResolver;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\DoctrineEntityRepository;
use Distantmagic\Resonance\Attribute\ResolvesHttpControllerParameter;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\DoctrineEntityManagerRepository;
use Distantmagic\Resonance\HttpControllerParameter;
use Distantmagic\Resonance\HttpControllerParameterResolution;
use Distantmagic\Resonance\HttpControllerParameterResolutionStatus;
use Distantmagic\Resonance\HttpControllerParameterResolver;
use Distantmagic\Resonance\SingletonCollection;
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * @template-extends HttpControllerParameterResolver<DoctrineEntityRepository>
 */
#[ResolvesHttpControllerParameter(DoctrineEntityRepository::class)]
#[Singleton(collection: SingletonCollection::HttpControllerParameterResolver)]
readonly class DoctrineEntityRepositoryResolver extends HttpControllerParameterResolver
{
    public function __construct(
        private DoctrineEntityManagerRepository $doctrineEntityManagerRepository,
    ) {}

    public function resolve(
        Request $request,
        Response $response,
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
