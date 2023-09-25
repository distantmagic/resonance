<?php

declare(strict_types=1);

namespace Resonance\SingletonProvider;

use Generator;
use LogicException;
use Resonance\Attribute\Singleton;
use Resonance\EsbuildMeta;
use Resonance\SingletonContainer;
use Resonance\SingletonProvider;
use RuntimeException;

/**
 * @template-extends SingletonProvider<EsbuildMeta>
 */
#[Singleton(provides: EsbuildMeta::class)]
final readonly class EsbuildMetaProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons): EsbuildMeta
    {
        $esbuildMeta = new EsbuildMeta();

        foreach ($this->entryPointImports($esbuildMeta) as $filename => $importPath) {
            $esbuildMeta->registerImport($filename, $importPath);
        }

        return $esbuildMeta;
    }

    /**
     * @return Generator<string,string>
     */
    private function entryPointImports(EsbuildMeta $esbuildMeta): Generator
    {
        foreach ($this->entryPointOutputs() as $filename => $output) {
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

                    yield $filename => $import->path;
                }
            }
        }
    }

    /**
     * @return Generator<string,object>
     */
    private function entryPointOutputs(): Generator
    {
        $esbuildMeta = $this->getEsbuildMetaDecoded();

        foreach ($esbuildMeta->outputs as $filename => $output) {
            if (!is_string($filename)) {
                throw new LogicException('Expected manifest outputs to be indexed with a string filename.');
            }

            if (!is_object($output)) {
                throw new LogicException('Manifest output is not an object.');
            }

            if (isset($output->entryPoint) && is_string($output->entryPoint)) {
                yield $filename => $output;
            }
        }
    }

    private function getEsbuildMetaContents(): string
    {
        if (!file_exists(DM_ESBUILD_META)) {
            throw new RuntimeException('Esbuild meta manifest does not exist.');
        }

        if (!is_readable(DM_ESBUILD_META)) {
            throw new RuntimeException('Esbuild meta manifest is not readable.');
        }

        return file_get_contents(DM_ESBUILD_META);
    }

    private function getEsbuildMetaDecoded(): object
    {
        $ret = json_decode(
            json: $this->getEsbuildMetaContents(),
            flags: JSON_THROW_ON_ERROR,
        );

        if (!is_object($ret)) {
            throw new LogicException('Expected manifest to be a JSON object.');
        }

        return $ret;
    }
}
