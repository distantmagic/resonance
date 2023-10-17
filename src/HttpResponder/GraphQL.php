<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder;

use Distantmagic\Resonance\Gatekeeper;
use Distantmagic\Resonance\GraphQLAdapter;
use Distantmagic\Resonance\GraphQLDatabaseQueryAdapter;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpResponder\Error\BadRequest;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\JsonTemplate;
use Distantmagic\Resonance\SwoolePromiseAdapter;
use Swoole\Http\Request;
use Swoole\Http\Response;

readonly class GraphQL extends HttpResponder
{
    public function __construct(
        private BadRequest $badRequest,
        private Gatekeeper $gatekeeper,
        private GraphQLAdapter $graphQLAdapter,
    ) {}

    public function respond(Request $request, Response $response): HttpInterceptableInterface|HttpResponderInterface
    {
        $requestContent = $request->getContent();

        if (empty($requestContent)) {
            return $this->badRequest;
        }

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

        $graphQLDatabaseQueryAdapter = new GraphQLDatabaseQueryAdapter($this->gatekeeper->withRequest($request));
        $swoolePromiseAdapter = new SwoolePromiseAdapter($graphQLDatabaseQueryAdapter);

        $result = $this->graphQLAdapter->query(
            $swoolePromiseAdapter,
            $query,
            null,
            null,
            $variables,
        );

        return new JsonTemplate($result);
    }
}
