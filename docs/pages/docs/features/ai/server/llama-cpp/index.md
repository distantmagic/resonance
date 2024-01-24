---
collections: 
    - documents
layout: dm:document
parent: docs/features/ai/server/index
title: llama.cpp
description: >
    Use Resonance to connect with llama.cpp server.
---

## llama.cpp

[llama.cpp](https://github.com/ggerganov/llama.cpp) is an open source framework
capable of running various LLM models.

It has a built-in HTTP server that supports continuous batching, parallel 
requests and is optimized for resouces usage.

You can use Resonance to connect with it and process LLM responses.

# Usage

You can also check the tutorial: {{tutorials/how-to-serve-llm-completions/index}}

## Configuration

All you need to do is add a configuration section that specifies the llama.cpp
server location:

```ini
[llamacpp]
host = 127.0.0.1
port = 8081
```

## Programmatic Use

In your class, you need to use {{docs/features/dependency-injection/index}} to
inject `LlamaCppClient`. Then, you need to use the appropriate 
{{docs/features/ai/prompt-engineering/index}} template adequate for the model 
you are serving. In the following example we will use 
[Mistral-Instruct](https://docs.mistral.ai/models/#chat-template) template:

```php
<?php

namespace App;

use Distantmagic\Resonance\LlamaCppClient;
use Distantmagic\Resonance\LlamaCppCompletionRequest;
use Distantmagic\Resonance\LlamaCppPromptTemplate\MistralInstructChat;

#[Singleton]
class LlamaCppGenerate 
{
    public function __construct(protected LlamaCppClient $llamaCppClient) 
    {
    }

    public function doSomething(): void
    {
        $template = new MistralInstructChat('How to make a cat happy?');
        $request = new LlamaCppCompletionRequest($template);

        $completion = $this->llamaCppClient->generateCompletion($request);

        // each token is a chunk of text, usually few-several letters returned
        // from the model you are using
        foreach ($completion as $token) {
            swoole_error_log(SWOOLE_LOG_DEBUG, (string) $token);
        }
    }
}
```

## Stopping Completion Generator

:::danger
Using just the `break` keyword does not stop the completion request. You need
to use `$completion->stop()`. 
:::

For example, stops after generating 10 tokens:

```php
$i = 0;

foreach ($completion as $token) {
    if ($i > 9) {
        $completion->stop();
    } else {
        // do something

        $i += 1;
    }
}
```
