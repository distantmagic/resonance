<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Generator;
use IteratorAggregate;
use LogicException;
use ReflectionClass;
use SplFileInfo;

/**
 * @template-implements IteratorAggregate<ReflectionClass>
 */
readonly class PHPFileReflectionClassIterator implements IteratorAggregate
{
    /**
     * @param iterable<SplFileInfo> $phpFileIterator
     */
    public function __construct(private iterable $phpFileIterator) {}

    /**
     * @return Generator<ReflectionClass>
     */
    public function getIterator(): Generator
    {
        foreach ($this->phpFileIterator as $file) {
            $fileBasename = $file->getBasename();

            $className = $this->readClassName($file);

            if ($className) {
                $reflectionClass = new ReflectionClass($className);
                $reflectionBasename = basename($reflectionClass->getFileName());

                if ($reflectionBasename !== $fileBasename) {
                    throw new LogicException('Class is not defined in the file named after the class: '.$fileBasename);
                }

                yield $reflectionClass;
            }
        }
    }

    /**
     * @return class-string
     */
    private function assertClassExists(string $className): string
    {
        if (class_exists($className) || interface_exists($className) || trait_exists($className)) {
            return $className;
        }

        throw new LogicException('Class could not be loaded: '.$className);
    }

    /**
     * @return null|class-string
     */
    private function readClassName(SplFileInfo $file): ?string
    {
        $fileBasename = $file->getBasename();

        if (ucfirst($fileBasename) !== $fileBasename) {
            return null;
        }

        if (str_starts_with($file->getPath(), DM_RESONANCE_ROOT)) {
            return $this->readKnownClassName($file, 'Distantmagic\\Resonance\\');
        }

        $namespace = (new PHPFileNamespaceReader($file))->readNamespace();

        if (is_null($namespace)) {
            return null;
        }

        $namespaceSequence = new PHPTokenSequenceMatcher([
            'T_CLASS',
            'T_WHITESPACE',
            'T_STRING',
        ]);

        foreach (new PHPFileTokenIterator($file) as $token) {
            $namespaceSequence->pushToken($token);

            if ($namespaceSequence->isMatching()) {
                $className = $namespace.'\\'.$namespaceSequence->matchingTokens->get(2)->text;

                return $this->assertClassExists($className);
            }
        }

        return null;
    }

    /**
     * This is an optimization to not tokenize all the files.
     *
     * @return class-string
     */
    private function readKnownClassName(SplFileInfo $file, string $namespace): string
    {
        $relativeFilename = str_replace(
            DIRECTORY_SEPARATOR,
            '\\',
            substr(
                trim(substr($file->getPathname(), strlen(DM_RESONANCE_ROOT)), DIRECTORY_SEPARATOR),
                0,
                -4,
            )
        );

        return $this->assertClassExists($namespace.$relativeFilename);
    }
}
