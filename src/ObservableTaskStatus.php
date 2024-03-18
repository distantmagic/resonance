<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum ObservableTaskStatus: string
{
    case Cancelled = 'Cancelled';
    case Failed = 'Failed';
    case Finished = 'Finished';
    case Pending = 'Pending';
    case Running = 'Running';
}
