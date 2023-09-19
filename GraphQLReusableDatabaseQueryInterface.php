<?php

declare(strict_types=1);

namespace Resonance;

use App\DatabaseQueryInterface;

/**
 * @template TResult
 *
 * @template-extends DatabaseQueryInterface<TResult>
 */
interface GraphQLReusableDatabaseQueryInterface extends DatabaseQueryInterface
{
    public function reusableQueryId(): string;
}
