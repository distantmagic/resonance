<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use Ds\Set;

readonly class PDOPoolConnectionBuilderCollection
{
    /**
     * @var Map<non-empty-string,Set<PDOPoolConnectionBuilderInterface>>
     */
    private Map $connectionBuilders;

    public function __construct()
    {
        $this->connectionBuilders = new Map();
    }

    /**
     * @param non-empty-string $name
     */
    public function addBuilder(
        string $name,
        PDOPoolConnectionBuilderInterface $pdoPoolConnectionBuilder,
    ): void {
        $this->getBuildersForConnection($name)->add($pdoPoolConnectionBuilder);
    }

    /**
     * @param non-empty-string $name
     *
     * @return Set<PDOPoolConnectionBuilderInterface>
     */
    public function getBuildersForConnection(string $name): Set
    {
        if (!$this->connectionBuilders->hasKey($name)) {
            $this->connectionBuilders->put($name, new Set());
        }

        return $this->connectionBuilders->get($name);
    }
}
