---
collections: 
    - documents
layout: dm:document
parent: docs/features/ai/server/llama-cpp/extractors/index
title: Subject
description: >
    Extract a specific phrase from user's input without modifying it.
---

# Subject

:::note
To use this feature you need to setup a 
{{docs/features/ai/server/llama-cpp/index}} server.
:::

`Subject` extracts a specific phrase from the user's input without modifying 
that phrase.

## What Is It Good For?

It is good for extracting specific phrases like application name, username
and any other strings that can be provided in an ambiguous way but need to 
be extracted exactly as-is.

For example:

```
User: I have a huge interest in cats and I want my profile to be called Cat Paradise.
Topic: application name
Result: Cat Paradise
```

```
User: Change my username to foo_bar
Topic: new username
Result: foo_bar
```

```
User: I would like to download a form AA-52XY. I really need it ASAP.
Topic: form id
Result: AA-52XY
```

# Usage

```php
<?php

use Distantmagic\Resonance\LlamaCppClientInterface;

/**
 * @var LlamaCppClientInterface $llamaCppClient
 */
$llamaCppExtractSubject = new LlamaCppExtractSubject(
    llamaCppClient: $llamaCppClient,
);

$response = $llamaCppExtractSubject->extract(
    input: 'Change my username to foo_bar',
    topic: 'new username',
);

assert('foo_bar' === $response->content);
```
