---
collections:
    - documents
layout: dm:document
parent: docs/features/vector-store/index
title: SQLite-VSS
description: >
    SQLite Extension for vector search
---

# SQLite-VSS

[SQLite-VSS](https://github.com/asg017/sqlite-vss) is a SQLite extension for 
vector search. It supports similarity search and uses 
[FAISS](https://faiss.ai/) under the hood.

# Usage

## Installation

You need PHP `sqlite3` extension.

To use SQLite with SQLite-VSS you need to load two additional extensions:
`vector0.so` and `vss0.so`. They are both available at the 
[SQLite-VSS](https://github.com/asg017/sqlite-vss/releases) release page.

After downloading them, you need to enable SQLite extensions in your `php.ini` 
file. Look for a similar section:

```ini
[sqlite3]
; Directory pointing to SQLite3 extensions
; https://php.net/sqlite3.extension-dir
;sqlite3.extension_dir =
```

You must configure a directory in `sqlite3.extension_dir`, and then put those
extensions into that directory.

## Configuration

You can configure extension filenames. Both are relative to 
`sqlite3.extension_dir` directory and both must be inside that directory:

```ini file:config.ini
[sqlite-vss]
extension_vector0 = vector0.so
extension_vss0 = vss0.so
```

## Creating Database Object

In your class you can then use `SQLiteVSSConnectionBuilder` to create an
`SQLite3` instance with VSS extensions loaded:

```php
use Distantmagic\Resonance\SQLiteVSSConnectionBuilder;
use SQLite3;

class MyClass
{
    private SQLite3 $sqlite;

    public function __construct(SQLiteVSSConnectionBuilder $builder)
    {
        $this->sqlite = $builder->buildConnection(':memory:');

        // This should select the currently installed SQLite-VSS version if 
        // everything is installed correctly.
        $this->sqlite->query('SELECT vss_version()')->fetchArray();
    }
}
```
