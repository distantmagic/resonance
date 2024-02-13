<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\ConfigurationFile;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use RuntimeException;

/**
 * @template-extends SingletonProvider<ConfigurationFile>
 */
#[Singleton(provides: ConfigurationFile::class)]
final readonly class ConfigurationFileProvider extends SingletonProvider
{
    private const INTERPOLATABLE_CONSTANTS = [
        'DM_APP_ROOT',
        'DM_PUBLIC_ROOT',
        'DM_RESONANCE_ROOT',
        'DM_ROOT',
    ];

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): ConfigurationFile
    {
        $filename = DM_ROOT.'/config.ini';
        $iniConfig = parse_ini_file(
            filename: $filename,
            process_sections: true,
            scanner_mode: INI_SCANNER_TYPED,
        );

        if (false === $iniConfig) {
            throw new RuntimeException('Unable to parse configuration file: '.$filename);
        }

        array_walk_recursive($iniConfig, $this->interpolateConstants(...));

        /**
         * @var array<string,mixed> $iniConfig
         */
        return new ConfigurationFile($iniConfig);
    }

    private function interpolateConstants(mixed &$value): void
    {
        if (!is_string($value)) {
            return;
        }

        foreach (self::INTERPOLATABLE_CONSTANTS as $interpolatableConstant) {
            $constantValue = constant($interpolatableConstant);

            if (!is_string($constantValue) || empty($constantValue)) {
                throw new RuntimeException(sprintf(
                    'You need to define "%s" constant in your constants.php file',
                    $interpolatableConstant,
                ));
            }

            $value = str_replace('%'.$interpolatableConstant.'%', $constantValue, $value);
        }
    }
}
