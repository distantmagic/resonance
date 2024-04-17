---
collections: 
    - documents
layout: dm:document
parent: docs/features/ai/server/llama-cpp/extractors/index
title: When
description: >
    Determine if user's input matched the condition formatted in a natural
    language.
---

# When

:::note
To use this feature you need to setup a 
{{docs/features/ai/server/llama-cpp/index}} server.
:::

`When` extractor is useful when determining if the user's input matches
the specific condition. It uses LLM to do the "reasoning," so the input
and the condition can be fuzzy (do not have to follow a strict syntax).

For example:

```
User: I want to create a blog
Condition: User asks about a cooking recipe
Result: No
```

## What Is It Good For?

Creating semi-scripted dialogues when you need to steer a conversation onto
specific topics or check if a user is talking about a specific topic.

# Usage

After setting up the server, you need to provide both user input and a 
condition to check - both in a natural language.

```php
<?php

use Distantmagic\Resonance\BackusNaurFormGrammar\YesNoMaybeGrammar;
use Distantmagic\Resonance\LlamaCppClientInterface;
use Distantmagic\Resonance\LlamaCppExtractWhen;
use Distantmagic\Resonance\YesNoMaybe;

/**
 * @var LlamaCppClientInterface $llamaCppClient
 * @var YesNoMaybeGrammar $yesNoMaybeGrammar
 */
$llamaCppExtractWhen = new LlamaCppExtractWhen(
    llamaCppClient: $llamaCppClient,
    yesNoMaybeGrammar: $yesNoMaybeGrammar,
);

$response = $llamaCppExtractWhen->extract(
    input: 'Why is my dog barking?',
    condition: 'User asks metaphysical questions',
);

// No, because a barking dog is not a metaphysical problem.
assert(YesNoMaybe::No === $response->result);
```
