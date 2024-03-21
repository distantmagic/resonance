<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use IteratorAggregate;

/**
 * @template-extends IteratorAggregate<ObservableTaskStatusUpdate>
 */
interface ObservableTaskInterface extends IteratorAggregate {}
