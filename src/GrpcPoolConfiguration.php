<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use SensitiveParameter;

readonly class GrpcPoolConfiguration
{
    /**
     * @param non-empty-string
     */
    public string $serverHostname;

    /**
     * @psalm-taint-source file $grpcPhpPluginBin
     * @psalm-taint-source file $outDirectory
     * @psalm-taint-source file $protocBin
     * @psalm-taint-source file $protoFile
     * @psalm-taint-source file $protosDirectory
     *
     * @param non-empty-string $grpcPhpPluginBin
     * @param non-empty-string $outDirectory
     * @param non-empty-string $protocBin
     * @param non-empty-string $protoFile
     * @param non-empty-string $protosDirectory
     */
    public function __construct(
        #[SensitiveParameter]
        public string $grpcPhpPluginBin,
        #[SensitiveParameter]
        public string $outDirectory,
        #[SensitiveParameter]
        public string $protocBin,
        #[SensitiveParameter]
        public string $protoFile,
        #[SensitiveParameter]
        public string $protosDirectory,
        #[SensitiveParameter]
        public string $serverHost,
        #[SensitiveParameter]
        public int $serverPort,
        public int $serverRequestRetryAttempts,
        public int $serverRequestRetryInterval,
    ) {
        $this->serverHostname = $this->serverHost.':'.$this->serverPort;
    }
}
