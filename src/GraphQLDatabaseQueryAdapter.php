<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\GraphqlSwoolePromiseAdapter\GraphQLResolverPromiseAdapterInterface;
use Distantmagic\Resonance\GraphQLResolverException\Forbidden;
use Distantmagic\Resonance\GraphQLResolverException\InvalidReturnType;
use Distantmagic\SwooleFuture\PromiseState;
use Distantmagic\SwooleFuture\SwooleFutureResult;
use Ds\Map;

/**
 * @template-implements GraphQLResolverPromiseAdapterInterface<GraphQLReusableDatabaseQueryInterface>
 */
readonly class GraphQLDatabaseQueryAdapter implements GraphQLResolverPromiseAdapterInterface
{
    /**
     * @var Map<string,SwooleFutureResult>
     */
    private Map $reusableFutures;

    public function __construct(private GatekeeperUserContext $gatekeeperUserContext)
    {
        $this->reusableFutures = new Map();
    }

    public function convertThenable(object $thenable): SwooleFutureResult
    {
        $reusableQueryId = $thenable::class.$thenable->reusableQueryId();

        if ($this->reusableFutures->hasKey($reusableQueryId)) {
            return $this->reusableFutures->get($reusableQueryId);
        }

        $future = $this->wrap($thenable);

        $this->reusableFutures->put($reusableQueryId, $future);

        return $future;
    }

    private function validateData(mixed $data): mixed
    {
        if (is_null($data)) {
            return null;
        }

        if (!is_object($data)) {
            return $data;
        }

        if (!($data instanceof CrudActionSubjectInterface)) {
            throw new InvalidReturnType('Expected database query result to be an instance of CrudActionSubjectInterface. Got: '.$data::class);
        }

        if (!$this->gatekeeperUserContext->canCrud($data, CrudAction::Read)) {
            throw new Forbidden('You do not have enough permissions to view this resource');
        }

        return $data;
    }

    private function wrap(GraphQLReusableDatabaseQueryInterface $reusableDatabaseQuery): SwooleFutureResult
    {
        $data = $reusableDatabaseQuery->execute();

        if ($reusableDatabaseQuery->isIterable()) {
            $ret = [];

            /**
             * @var mixed $dataItem explicitly mixed for typechecks
             */
            foreach ($data as $dataItem) {
                array_push($ret, $this->validateData($dataItem));
            }

            return new SwooleFutureResult(PromiseState::Fulfilled, $ret);
        }

        /**
         * @var mixed $validatedData explicitly mixed for typechecks
         */
        $validatedData = $this->validateData($data);

        return new SwooleFutureResult(PromiseState::Fulfilled, $validatedData);
    }
}
