<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Symfony\Component\Console\Command\Command as SymfonyCommand;

abstract class Command extends SymfonyCommand
{
    /**
     * Do not use Symfony's command constructor arguments to make it easier on
     * the DI.
     */
    public function __construct()
    {
        parent::__construct();
    }
}
