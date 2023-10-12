<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Dflydev\DotAccessData\Data;
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
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): ConfigurationFile
    {
        $filename = DM_ROOT.'/config.ini';
        $iniConfig = parse_ini_file(
            filename: $filename,
            process_sections: true,
            scanner_mode: INI_SCANNER_TYPED,
        );

        if (!$iniConfig) {
            throw new RuntimeException('Unable to parse configuration file: '.$filename);
        }

        /**
         * @var array<string,mixed> $iniConfig
         */
        return new ConfigurationFile(new Data($iniConfig));
    }
}
