<?php

declare(strict_types=1);

namespace Resonance\Event;

use Resonance\Event;

readonly class SQLQueryBeforeExecute extends Event
{
    public function __construct(public string $sql) {}
}
