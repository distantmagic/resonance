<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Symfony\Component\Console\Command\Command as SymfonyCommand;

abstract class Command extends SymfonyCommand
{
    public function __construct()
    {
        /**
         * Force the 'name' to be ignored. It's easier to handle DI
         * dependencies that way.
         */
        parent::__construct();
    }
}
