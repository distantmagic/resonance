<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum ObservableTaskStatus: string
{
    case Cancelled = 'cancelled';
    case Failed = 'failed';
    case Finished = 'finished';
    case Pending = 'pending';
    case Running = 'running';
    case TimedOut = 'timed_out';

    public function isFinal(): bool
    {
        return match ($this) {
            ObservableTaskStatus::Cancelled,
            ObservableTaskStatus::Failed,
            ObservableTaskStatus::Finished,
            ObservableTaskStatus::TimedOut => true,
            default => false,
        };
    }
}
