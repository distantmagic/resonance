<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpControllerParameterResolver;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\DoctrineEntityRouteParameter;
use Distantmagic\Resonance\Attribute\ResolvesHttpControllerParameter;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\CrudActionSubjectInterface;
use Distantmagic\Resonance\DoctrineEntityManagerRepository;
use Distantmagic\Resonance\Gatekeeper;
use Distantmagic\Resonance\HttpControllerParameter;
use Distantmagic\Resonance\HttpControllerParameterResolution;
use Distantmagic\Resonance\HttpControllerParameterResolutionStatus;
use Distantmagic\Resonance\HttpControllerParameterResolver;
use Distantmagic\Resonance\HttpRouteMatchRegistry;
use Distantmagic\Resonance\SingletonCollection;
use LogicException;
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * @template-extends HttpControllerParameterResolver<DoctrineEntityRouteParameter>
 */
#[ResolvesHttpControllerParameter(DoctrineEntityRouteParameter::class)]
#[Singleton(collection: SingletonCollection::HttpControllerParameterResolver)]
readonly class DoctrineEntityRouteParameterResolver extends HttpControllerParameterResolver
{
    public function __construct(
        private DoctrineEntityManagerRepository $doctrineEntityManagerRepository,
        private Gatekeeper $gatekeeper,
        private HttpRouteMatchRegistry $routeMatchRegistry,
    ) {}

    public function resolve(
        Request $request,
        Response $response,
        HttpControllerParameter $parameter,
        Attribute $attribute,
    ): HttpControllerParameterResolution {
        $routeParameterValue = $this->routeMatchRegistry->get($request)->routeVars->get($attribute->from, null);

        if (is_null($routeParameterValue)) {
            return new HttpControllerParameterResolution(HttpControllerParameterResolutionStatus::MissingUrlParameterValue);
        }

        $entityManager = $this
            ->doctrineEntityManagerRepository
            ->getEntityManager($request, $attribute->connection)
        ;

        $entityRepository = $entityManager->getRepository($parameter->className);

        $entity = $entityRepository->findOneBy([
            $attribute->lookupField => $routeParameterValue,
        ]);

        if (is_null($entity)) {
            return new HttpControllerParameterResolution(HttpControllerParameterResolutionStatus::NotFound);
        }

        if (!($entity instanceof CrudActionSubjectInterface)) {
            throw new LogicException('Bound entity cannot be subjected to Gatekeeper check');
        }

        if (!$this->gatekeeper->withRequest($request)->canCrud($entity, $attribute->intent)) {
            return new HttpControllerParameterResolution(HttpControllerParameterResolutionStatus::Forbidden);
        }

        return new HttpControllerParameterResolution(
            HttpControllerParameterResolutionStatus::Success,
            $entity,
        );
    }
}
