<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use PDO;

interface PDOPoolConnectionBuilderInterface
{
    public function buildPDOConnection(PDO $pdo): PDO;
}
