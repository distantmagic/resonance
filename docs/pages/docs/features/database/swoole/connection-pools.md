---
collections: 
    - name: documents
      next: docs/features/database/swoole/database-queries
layout: dm:document
next: docs/features/database/swoole/database-queries
parent: docs/features/database/swoole/index
title: Connection Pools
description: >
    Use convenient connections pool wrapper for database interaction.
---

# Connection Pools

When your application starts, it automatically creates a pool of connections. 
When a query is about to be executed, it borrows a connection from the pool. 
Connection Pool is responsible for rotating and reestablishing those 
connections.

If any of the connections happen to close for any reason, Swoole reopens them 
in the background and ensures that the connection pool never becomes depleted.

To simplify the process and eliminate the need for manual borrowing and 
returning connections to and from the pool, Resonance offers a convenient 
wrapper, which consists of several tools, for example: 
{{docs/features/database/swoole/database-queries}}, 
{{docs/features/database/swoole/database-entities}}. 

Follow this documentation page for configuration instructions and the following
pages for specific use cases.

# Configuration

If you need just one connection pool, you can use the `default` connection 
namespace in the configuration file

```ini file:config.ini
; ...
[database]
default[driver] = mysql
default[host] = 127.0.0.1
default[port] = 3306
default[database] = distantmagic
default[username] = distantmagic
default[password] = distantmagic
default[log_queries] = false
default[pool_prefill] = true
default[pool_size] = 8
; ...
```

If you need more (like for read-only database cluster and write-only database 
cluster), you can add more configuration options:

```ini file:config.ini
; ...
[database]

readonly[driver] = mysql
readonly[host] = 192.168.1.50
readonly[port] = 3306
readonly[database] = distantmagic
readonly[username] = distantmagic
readonly[password] = distantmagic
readonly[log_queries] = false
readonly[pool_prefill] = true
readonly[pool_size] = 8

writeonly[driver] = mysql
writeonly[host] = 192.168.1.51
writeonly[port] = 3306
writeonly[database] = distantmagic
writeonly[username] = distantmagic
writeonly[password] = distantmagic
writeonly[log_queries] = false
writeonly[pool_prefill] = true
writeonly[pool_size] = 8
; ...
```

Those connection pools are lazy-loaded, which means that they are only going to
be established if they are used by the 
{{docs/features/dependency-injection/index}}.

# Usage

See usage at {{docs/features/database/swoole/database-queries}}.
