<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\RequiresPhpExtension;
use Distantmagic\Resonance\Attribute\Singleton;
use SQLite3;

#[RequiresPhpExtension('sqlite3')]
#[Singleton]
readonly class SQLiteVSSConnectionBuilder
{
    public function __construct(private SQLiteVSSConfiguration $sqliteVSSConfiguration) {}

    public function buildConnection(
        string $filename,
        int $flags = SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE,
        string $encryptionKey = '',
    ): SQLite3 {
        $sqlite = new SQLite3($filename, $flags, $encryptionKey);
        $sqlite->loadExtension($this->sqliteVSSConfiguration->extensionVector0);
        $sqlite->loadExtension($this->sqliteVSSConfiguration->extensionVss0);

        return $sqlite;
    }
}
