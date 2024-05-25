<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder;

use Distantmagic\GraphqlSwoolePromiseAdapter\SwoolePromiseAdapter;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Gatekeeper;
use Distantmagic\Resonance\GraphQLAdapter;
use Distantmagic\Resonance\GraphQLDatabaseQueryAdapter;
use Distantmagic\Resonance\GraphQLReusableDatabaseQueryInterface;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpResponder\Error\BadRequest;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\JsonTemplate;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[Singleton]
final readonly class GraphQL extends HttpResponder
{
    public function __construct(
        private BadRequest $badRequest,
        private Gatekeeper $gatekeeper,
        private GraphQLAdapter $graphQLAdapter,
    ) {}

    public function respond(ServerRequestInterface $request, ResponseInterface $response): HttpInterceptableInterface|HttpResponderInterface
    {
        $requestContent = $request->getBody()->getContents();

        $requestInput = json_decode(
            json: $requestContent,
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );

        if (
            !is_array($requestInput)
            || !isset($requestInput['query'])
            || !is_string($requestInput['query'])
            || (
                array_key_exists('variables', $requestInput)
                && !is_array($requestInput['variables'])
                && !is_null($requestInput['variables'])
            )
        ) {
            return $this->badRequest;
        }

        $query = $requestInput['query'];

        /**
         * @var null|array<string,mixed> $variables
         */
        $variables = $requestInput['variables'] ?? null;

        $swoolePromiseAdapter = new SwoolePromiseAdapter();
        $swoolePromiseAdapter
            ->resolverPromiseAdapterRegistry
            ->registerResolverPromiseAdapter(
                GraphQLReusableDatabaseQueryInterface::class,
                new GraphQLDatabaseQueryAdapter($this->gatekeeper->withRequest($request)),
            )
        ;

        return new JsonTemplate(
            $request,
            $response,
            $this->graphQLAdapter->query(
                $swoolePromiseAdapter,
                $query,
                null,
                $request,
                $variables,
            ),
        );
    }
}
