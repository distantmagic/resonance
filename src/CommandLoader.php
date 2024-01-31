<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Attribute\WantsFeature;
use Ds\Map;
use ReflectionClass;
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
                return $this->makeCommand($phpProjectFileAttribute);
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

    /**
     * @param PHPFileReflectionClassAttribute<Command,ConsoleCommand> $phpProjectFileAttribute
     */
    private function makeCommand(PHPFileReflectionClassAttribute $phpProjectFileAttribute): Command
    {
        $className = $phpProjectFileAttribute->reflectionClass->getName();

        $reflectionClass = new ReflectionClass($className);
        $reflectionClassAttributeManager = new ReflectionClassAttributeManager($reflectionClass);

        foreach ($reflectionClassAttributeManager->findAttributes(WantsFeature::class) as $wantedFeature) {
            $this->container->enableFeature($wantedFeature->feature);
        }

        return $this->container->make($className);
    }
}
