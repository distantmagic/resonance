<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Closure;
use Generator;
use IteratorAggregate;
use LogicException;
use ReflectionFunction;
use RuntimeException;
use SplFileInfo;

/**
 * @template-implements IteratorAggregate<ReflectionFunction>
 */
readonly class PHPFileReflectionFunctionIterator implements IteratorAggregate
{
    /**
     * @param iterable<SplFileInfo> $phpFileIterator
     */
    public function __construct(private iterable $phpFileIterator) {}

    /**
     * @return Generator<ReflectionFunction>
     */
    public function getIterator(): Generator
    {
        foreach ($this->phpFileIterator as $file) {
            $fileBasename = $file->getBasename();

            $functionName = $this->readFunctionName($file);

            if ($functionName) {
                $reflectionFunction = new ReflectionFunction($functionName);
                $reflectionBasename = basename($reflectionFunction->getFileName());

                if ($reflectionBasename !== $fileBasename) {
                    throw new LogicException('Function is not defined in the file named after the function: '.$fileBasename);
                }

                yield $reflectionFunction;
            }
        }
    }

    /**
     * @psalm-suppress UnresolvableInclude require_once is used to load the function
     *
     * @return callable-string
     */
    private function assertFunctionExists(SplFileInfo $file, string $functionName): string
    {
        $requireClosure = Closure::fromCallable(function (SplFileInfo $file) {
            require_once $file->getPathname();
        })->bindTo(null);

        if ($requireClosure) {
            $requireClosure($file);
        }

        if (function_exists($functionName)) {
            return $functionName;
        }

        throw new LogicException(sprintf(
            'Function could not be loaded: "%s" in file "%s"',
            $functionName,
            $file->getPathname(),
        ));
    }

    /**
     * @return null|callable-string
     */
    private function readFunctionName(SplFileInfo $file): ?string
    {
        $fileBasename = $file->getBasename();

        if (ucfirst($fileBasename) !== $fileBasename) {
            return null;
        }

        $namespace = (new PHPFileNamespaceReader($file))->readNamespace();

        if (is_null($namespace)) {
            return null;
        }

        $fileBasenameWithoutExtension = $file->getBasename('.php');

        $namespaceSequence = new PHPTokenSequenceMatcher([
            'T_WHITESPACE',
            'T_FUNCTION',
            'T_WHITESPACE',
            'T_STRING',
        ]);

        foreach (new PHPFileTokenIterator($file) as $token) {
            if (match ($token->getTokenName()) {
                'T_CLASS', 'T_ENUM', 'T_INTERFACE', 'T_TRAIT' => true,
                default => false,
            }) {
                return null;
            }

            $namespaceSequence->pushToken($token);

            if ($namespaceSequence->isMatching()) {
                $baseFunctionName = $namespaceSequence->matchingTokens->get(3)->text;
                $functionName = $namespace.'\\'.$baseFunctionName;

                if ($fileBasenameWithoutExtension !== $baseFunctionName && strtolower($baseFunctionName) === strtolower($fileBasenameWithoutExtension)) {
                    throw new RuntimeException('Function name must match the file name: '.$file->getPathname());
                }

                if ($fileBasenameWithoutExtension === $baseFunctionName) {
                    return $this->assertFunctionExists($file, $functionName);
                }
            }
        }

        return null;
    }
}
