<?php

declare(strict_types=1);

namespace Resonance;

use Ds\Set;
use LogicException;

readonly class EsbuildMetaEntryPoints
{
    /**
     * @var Set<string> $preloadables
     */
    public Set $preloadables;

    public function __construct(private EsbuildMeta $esbuildMeta)
    {
        $this->preloadables = new Set();
    }

    public function preloadEntryPoint(string $entryPointBasename): void
    {
        $entryPointPath = $this->resolveEntryPointPath($entryPointBasename);

        $this->preloadables->add($entryPointPath);
    }

    public function resolveEntryPointPath(string $entryPointBasename): string
    {
        if (!$this->esbuildMeta->basenames->hasKey($entryPointBasename)) {
            throw new LogicException('There is no entrypoint named: '.$entryPointBasename);
        }

        $entryPointPath = $this->esbuildMeta->basenames->get($entryPointBasename);

        foreach ($this->esbuildMeta->imports->get($entryPointPath) as $path) {
            $this->preloadables->add($path);
        }

        return $entryPointPath;
    }
}
