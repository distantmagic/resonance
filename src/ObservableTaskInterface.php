<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use IteratorAggregate;

/**
 * @template TTaskStatus of ObservableTaskStatusUpdate
 *
 * @template-extends IteratorAggregate<TTaskStatus>
 */
interface ObservableTaskInterface extends IteratorAggregate {}
