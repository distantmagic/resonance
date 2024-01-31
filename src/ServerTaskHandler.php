<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TTask of object
 *
 * @template-implements ServerTaskHandlerInterface<TTask>
 */
abstract readonly class ServerTaskHandler implements ServerTaskHandlerInterface {}
