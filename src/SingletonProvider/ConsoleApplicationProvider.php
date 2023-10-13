<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\ConsoleApplication;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<ConsoleApplication>
 */
#[Singleton(
    provides: ConsoleApplication::class,
    requiresCollection: SingletonCollection::ConsoleCommand,
)]
final readonly class ConsoleApplicationProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): ConsoleApplication
    {
        $consoleApplication = new ConsoleApplication();
        $consoleApplication->setAutoExit(false);
        $consoleApplication->setName('Resonance');

        foreach ($this->collectCommands($singletons) as $commandAttribute) {
            $attribute = $commandAttribute->attribute;
            $command = $commandAttribute->singleton;

            $command->setName($attribute->name);

            if ($attribute->description) {
                $command->setDescription($attribute->description);
            }

            $consoleApplication->add($command);
        }

        return $consoleApplication;
    }

    /**
     * @return iterable<SingletonAttribute<Command,ConsoleCommand>>
     */
    private function collectCommands(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            Command::class,
            ConsoleCommand::class,
        );
    }
}
