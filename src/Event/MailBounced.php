<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Event;

use Distantmagic\Resonance\Event;
use Distantmagic\Resonance\MailBounceReportInterface;

/**
 * @psalm-suppress PossiblyUnusedProperty used in listeners
 */
final readonly class MailBounced extends Event
{
    public function __construct(public MailBounceReportInterface $report) {}
}
