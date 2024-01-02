<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Cron\CronExpression;
use Distantmagic\Resonance\Attribute as BaseAttribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
final readonly class ScheduledWithCron extends BaseAttribute
{
    public CronExpression $expression;

    /**
     * @param non-empty-string $expression
     */
    public function __construct(
        string $expression,
        public ?string $name = null,
    ) {
        $this->expression = new CronExpression($expression);
    }
}
