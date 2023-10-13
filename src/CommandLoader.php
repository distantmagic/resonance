<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Ds\Map;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LazyCommand;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;

readonly class CommandLoader implements CommandLoaderInterface
{
    /**
     * @var Map<string,PHPFileReflectionClassAttribute<Command,ConsoleCommand>>
     */
    private Map $names;

    public function __construct(
        private DependencyInjectionContainer $container,
        PHPProjectFiles $phpProjectFiles,
    ) {
        $this->names = new Map();

        /**
         * @var iterable<PHPFileReflectionClassAttribute<Command,ConsoleCommand>>
         */
        $matchedPhpProjectFiles = $phpProjectFiles->findByAttribute(ConsoleCommand::class);

        foreach ($matchedPhpProjectFiles as $phpProjectFile) {
            $this->names->put($phpProjectFile->attribute->name, $phpProjectFile);
        }
    }

    /**
     * @throws CommandNotFoundException
     */
    public function get(string $name): Command
    {
        if (!$this->has($name)) {
            throw new CommandNotFoundException('Command not found: '.$name);
        }

        $phpProjectFileAttribute = $this->names->get($name);
        $attribute = $phpProjectFileAttribute->attribute;

        return new LazyCommand(
            aliases: $attribute->aliases,
            description: (string) $attribute->description,
            isEnabled: $attribute->isEnabled,
            isHidden: $attribute->isHidden,
            name: $attribute->name,
            commandFactory: function () use ($phpProjectFileAttribute): Command {
                return $this->container->make($phpProjectFileAttribute->reflectionClass->getName());
            },
        );
    }

    public function getNames(): array
    {
        return $this->names->keys()->toArray();
    }

    public function has(string $name): bool
    {
        return $this->names->hasKey($name);
    }
}
