<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Ds\Map;
use Generator;
use LogicException;
use RuntimeException;

#[Singleton]
readonly class EsbuildMetaBuilder
{
    /**
     * @var Map<string,EsbuildMeta>
     */
    private Map $esbuildMetaCache;

    public function __construct(private JsonSerializer $jsonSerializer)
    {
        $this->esbuildMetaCache = new Map();
    }

    public function build(
        string $esbuildMetafile,
        ?string $stripOutputPrefix = null,
    ): EsbuildMeta {
        if ($this->esbuildMetaCache->hasKey($esbuildMetafile)) {
            return $this->esbuildMetaCache->get($esbuildMetafile);
        }

        $esbuildMeta = $this->doBuild($esbuildMetafile, $stripOutputPrefix);

        $this->esbuildMetaCache->put($esbuildMetafile, $esbuildMeta);

        return $esbuildMeta;
    }

    private function doBuild(
        string $esbuildMetafile,
        ?string $stripOutputPrefix = null,
    ): EsbuildMeta {
        $esbuildMeta = new EsbuildMeta();

        if (!file_exists($esbuildMetafile)) {
            // Nothing to be done here
            return $esbuildMeta;
        }

        foreach (
            $this->entryPointImports(
                $esbuildMetafile,
                $stripOutputPrefix,
                $esbuildMeta,
            ) as $filename => $importPath
        ) {
            $esbuildMeta->registerImport($filename, $importPath);
        }

        return $esbuildMeta;
    }

    /**
     * @return Generator<string,string>
     */
    private function entryPointImports(
        string $esbuildMetafile,
        ?string $stripOutputPrefix,
        EsbuildMeta $esbuildMeta,
    ): Generator {
        foreach ($this->entryPointOutputs($esbuildMetafile, $stripOutputPrefix) as $filename => $output) {
            if (!isset($output->entryPoint) || !is_string($output->entryPoint)) {
                throw new LogicException('Output entry point was expected to be a string.');
            }

            $entryPointBasename = basename($output->entryPoint);
            $esbuildMeta->registerEntryPoint($entryPointBasename, $filename);

            if (!isset($output->imports)) {
                continue;
            }
            if (!is_array($output->imports)) {
                continue;
            }
            /**
             * @var mixed $import explicitly mixed for typechecks
             */
            foreach ($output->imports as $import) {
                if (!is_object($import)) {
                    throw new LogicException('Expected entrypoint import defintion to be an object.');
                }

                if (
                    !isset($import->kind, $import->path)
                    || !is_string($import->kind)
                    || !is_string($import->path)
                ) {
                    throw new LogicException('Expected "kind" and "path" import fields to be set.');
                }

                yield $filename => $this->stripBaseDirectory(
                    $esbuildMetafile,
                    $stripOutputPrefix,
                    $import->path,
                );
            }
        }
    }

    /**
     * @return Generator<string,object>
     */
    private function entryPointOutputs(
        string $esbuildMetafile,
        ?string $stripOutputPrefix,
    ): Generator {
        $esbuildMeta = $this->getEsbuildMetaDecoded($esbuildMetafile);

        foreach ($esbuildMeta->outputs as $filename => $output) {
            if (!is_string($filename)) {
                throw new LogicException('Expected manifest outputs to be indexed with a string filename.');
            }

            if (!is_object($output)) {
                throw new LogicException('Manifest output is not an object.');
            }
            if (!isset($output->entryPoint)) {
                continue;
            }
            if (!is_string($output->entryPoint)) {
                continue;
            }
            yield $this->stripBaseDirectory(
                $esbuildMetafile,
                $stripOutputPrefix,
                $filename,
            ) => $output;
        }
    }

    private function getEsbuildMetaContents(string $esbuildMetafile): string
    {
        if (!file_exists($esbuildMetafile)) {
            throw new RuntimeException('Esbuild meta manifest does not exist: '.$esbuildMetafile);
        }

        if (!is_readable($esbuildMetafile)) {
            throw new RuntimeException('Esbuild meta manifest is not readable: '.$esbuildMetafile);
        }

        $content = file_get_contents($esbuildMetafile);

        if (!is_string($content)) {
            throw new RuntimeException('Unable to read esbuild manifest: '.$esbuildMetafile);
        }

        return $content;
    }

    private function getEsbuildMetaDecoded(string $esbuildMetafile): object
    {
        $ret = $this
            ->jsonSerializer
            ->unserialize($this->getEsbuildMetaContents($esbuildMetafile))
        ;

        if (!is_object($ret)) {
            throw new LogicException('Expected manifest to be a JSON object.');
        }

        return $ret;
    }

    private function stripBaseDirectory(
        string $esbuildMetafile,
        ?string $stripOutputPrefix,
        string $filename,
    ): string {
        if (is_null($stripOutputPrefix)) {
            return $filename;
        }

        $absoluteFilename = dirname($esbuildMetafile).'/'.$filename;

        if (!str_starts_with($absoluteFilename, $stripOutputPrefix)) {
            return $filename;
        }

        return substr($absoluteFilename, strlen($stripOutputPrefix));
    }
}
