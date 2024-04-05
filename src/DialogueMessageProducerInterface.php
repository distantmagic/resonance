<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use IteratorAggregate;
use Stringable;

/**
 * @template-extends IteratorAggregate<DialogueMessageChunk>
 */
interface DialogueMessageProducerInterface extends IteratorAggregate, Stringable {}
