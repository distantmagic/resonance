---
collections: 
    - documents
layout: dm:document
parent: docs/features/ai/server/llama-cpp/extractors/index
title: Yes-No-Maybe
description: >
    Determine if user's response is affrimative or not.
---

# Yes-No-Maybe

:::note
To use this feature you need to setup a 
{{docs/features/ai/server/llama-cpp/index}} server.
:::

Maps generally affirmative or negatory answers into strictly `yes`, `no` or
`maybe`.

For example:

- `I CONFIRM` becomes  `Yes`
- `yes` becomes  `Yes`
- `i do not want that` becomes  `No`
- `no` becomes  `No`
- `i dont know` becomes `Maybe`
- `maybe` becomes  `Maybe`

## What Is It Good For?

This extractor is useful if you expect fuzzy inputs like `yeah`, `i agree`,
`i confirm` etc and you need to map them into a more specific output.

# Usage

After setting up the {{docs/features/ai/server/llama-cpp/index}} you only
need to provide user's input. It will then be mapped to either `yes`, `no` or
`maybe`.

Resonance uses `YesNoMaybe` enum for consistency.

```php
<?php

use Distantmagic\Resonance\BackusNaurFormGrammar\YesNoMaybeGrammar;
use Distantmagic\Resonance\LlamaCppClientInterface;
use Distantmagic\Resonance\LlamaCppExtractYesNoMaybe;
use Distantmagic\Resonance\YesNoMaybe;

/**
 * @var LlamaCppClientInterface $llamaCppClient
 * @var YesNoMaybeGrammar $yesNoMaybeGrammar
 */
$llamaCppExtractYesNoMaybe = new LlamaCppExtractYesNoMaybe(
    llamaCppClient: $llamaCppClient,
    yesNoMaybeGrammar: $yesNoMaybeGrammar,
);

$response = $llamaCppExtractYesNoMaybe->extract(
    input: 'Yes! I do want that to happen!',
);

assert(YesNoMaybe::Yes === $response->result);
```
