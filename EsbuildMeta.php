<?php

declare(strict_types=1);

namespace Resonance;

use Ds\Map;
use Ds\Set;
use LogicException;

readonly class EsbuildMeta
{
    /**
     * @var Map<string,string>
     */
    public Map $basenames;

    /**
     * @var Map<string,Set<string>>
     */
    public Map $imports;

    public function __construct()
    {
        $this->basenames = new Map();
        $this->imports = new Map();
    }

    public function registerEntryPoint(string $entryPointBasename, string $path): void
    {
        if ($this->basenames->hasKey($entryPointBasename)) {
            if ($this->basenames->get($entryPointBasename) !== $path) {
                throw new LogicException('Ambiguous entrypoint resolution: '.$entryPointBasename);
            }
        } else {
            $this->basenames->put($entryPointBasename, $path);

            if ($this->imports->hasKey($path)) {
                throw new LogicException('Imports Set is already created: '.$entryPointBasename);
            }

            $this->imports->put($path, new Set());
        }
    }

    public function registerImport(string $filename, string $importPath): void
    {
        $filenameImports = $this->imports->get($filename);

        if ($filenameImports->contains($importPath)) {
            throw new LogicException('Duplicate import '.$filename.' -> '.$importPath);
        }

        $filenameImports->add($importPath);
    }
}
