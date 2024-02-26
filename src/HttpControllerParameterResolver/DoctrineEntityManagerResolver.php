<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpControllerParameterResolver;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\DoctrineEntityManager;
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
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Response;

/**
 * @template-extends HttpControllerParameterResolver<DoctrineEntityManager>
 */
#[GrantsFeature(Feature::Doctrine)]
#[ResolvesHttpControllerParameter(DoctrineEntityManager::class)]
#[Singleton(collection: SingletonCollection::HttpControllerParameterResolver)]
readonly class DoctrineEntityManagerResolver extends HttpControllerParameterResolver
{
    public function __construct(
        private DoctrineEntityManagerRepository $doctrineEntityManagerRepository,
    ) {}

    public function resolve(
        ServerRequestInterface $request,
        Response $response,
        HttpControllerParameter $parameter,
        Attribute $attribute,
    ): HttpControllerParameterResolution {
        return new HttpControllerParameterResolution(
            HttpControllerParameterResolutionStatus::Success,
            $this->doctrineEntityManagerRepository->getEntityManager($request, $attribute->connection),
        );
    }
}
