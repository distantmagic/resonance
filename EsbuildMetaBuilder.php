<?php

declare(strict_types=1);

namespace Resonance;

use Generator;
use LogicException;
use RuntimeException;

readonly class EsbuildMetaBuilder
{
    public function build(
        string $esbuildMetafile,
        string $stripOutputPrefix = '',
    ): EsbuildMeta {
        $esbuildMeta = new EsbuildMeta();

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
        string $stripOutputPrefix,
        EsbuildMeta $esbuildMeta,
    ): Generator {
        foreach ($this->entryPointOutputs($esbuildMetafile, $stripOutputPrefix) as $filename => $output) {
            if (!isset($output->entryPoint) || !is_string($output->entryPoint)) {
                throw new LogicException('Output entry point was expected to be a string.');
            }

            $entryPointBasename = basename($output->entryPoint);

            $esbuildMeta->registerEntryPoint($entryPointBasename, $filename);

            if (isset($output->imports) && is_array($output->imports)) {
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

                    yield $filename => $this->stripBaseDirectory($stripOutputPrefix, $import->path);
                }
            }
        }
    }

    /**
     * @return Generator<string,object>
     */
    private function entryPointOutputs(
        string $esbuildMetafile,
        string $stripOutputPrefix,
    ): Generator {
        $esbuildMeta = $this->getEsbuildMetaDecoded($esbuildMetafile);

        foreach ($esbuildMeta->outputs as $filename => $output) {
            if (!is_string($filename)) {
                throw new LogicException('Expected manifest outputs to be indexed with a string filename.');
            }

            if (!is_object($output)) {
                throw new LogicException('Manifest output is not an object.');
            }

            if (isset($output->entryPoint) && is_string($output->entryPoint)) {
                yield $this->stripBaseDirectory($stripOutputPrefix, $filename) => $output;
            }
        }
    }

    private function getEsbuildMetaContents(string $esbuildMetafile): string
    {
        if (!file_exists($esbuildMetafile)) {
            throw new RuntimeException('Esbuild meta manifest does not exist.');
        }

        if (!is_readable($esbuildMetafile)) {
            throw new RuntimeException('Esbuild meta manifest is not readable.');
        }

        return file_get_contents($esbuildMetafile);
    }

    private function getEsbuildMetaDecoded(string $esbuildMetafile): object
    {
        $ret = json_decode(
            json: $this->getEsbuildMetaContents($esbuildMetafile),
            flags: JSON_THROW_ON_ERROR,
        );

        if (!is_object($ret)) {
            throw new LogicException('Expected manifest to be a JSON object.');
        }

        return $ret;
    }

    private function stripBaseDirectory(string $stripOutputPrefix, string $filename): string
    {
        if (!str_starts_with($filename, $stripOutputPrefix)) {
            return $filename;
        }

        return substr($filename, strlen($stripOutputPrefix));
    }
}
