---
collections: 
    - name: documents
      next: docs/features/database/migrations/index
layout: dm:document
parent: docs/features/database/index
title: Doctrine
description: >
    Use Doctrine on top of Swoole's connection pools and Resonance to obtain
    it's ORM features.
---

# Doctrine

Resonance provides integration with Doctrine's 
[DBAL](https://www.doctrine-project.org/projects/dbal.html)
and [ORM](https://www.doctrine-project.org/projects/orm.html). 

Doctrine integration uses the same database configuration as 
{{docs/features/database/swoole/index}} database integration.

## Under the Hood

The integration is opinionated, so we picked the configuration defaults to best 
match the Swoole environment.

Those are:

1. Doctrine uses in-memory local cache. No file cache nor Redis cache is 
    necessary because all the metadata is loaded just once, and Doctrine keeps 
    it in a PHP array.
2. Doctrine uses only attribute mapping. That is in line with other 
    Resonance's features.
3. Doctrine integration uses {{docs/features/database/swoole/connection-pools}}
    and it does so transparently.

:::tip
If you just want to make a plain SQL query and you don't need a query builder
you might consider using plain 
{{docs/features/database/swoole/database-queries}} withtout the overhead of 
Doctrine.
:::

{{docs/features/database/doctrine/*!docs/features/database/doctrine/index}}
