<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TResult
 *
 * @template-extends DatabaseQueryInterface<TResult>
 */
interface GraphQLReusableDatabaseQueryInterface extends DatabaseQueryInterface
{
    public function reusableQueryId(): string;
}
