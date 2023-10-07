<?php

declare(strict_types=1);

namespace Resonance;

/**
 * @template TSubject of CrudActionSubjectInterface
 *
 * @template-implements CrudActionGateInterface<TSubject>
 */
abstract readonly class CrudActionGate implements CrudActionGateInterface {}
