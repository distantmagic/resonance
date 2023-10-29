<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum DatabaseConnectionPoolDriverName: string
{
    use EnumValuesTrait;

    case MySQL = 'mysql';
    case PostgreSQL = 'postgresql';
    case SQLite = 'sqlite';
}
