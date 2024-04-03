<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use IteratorAggregate;

/**
 * @template-extends IteratorAggregate<DialogueMessageChunk>
 */
interface DialogueMessageProducerInterface extends IteratorAggregate {}
