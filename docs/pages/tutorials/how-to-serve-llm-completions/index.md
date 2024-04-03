---
collections:
  - tutorials
layout: dm:tutorial
parent: tutorials/index
tags:
    - llama.cpp
    - LLM
title: How to Serve LLM Completions (With llama.cpp)?
description: >
    How to connect with llama.cpp and issue parallel requests for LLM 
    completions and embeddings with Resonance.
---

## Preparations

To start, you need to compile 
[llama.cpp](https://github.com/ggerganov/llama.cpp). You can follow their 
[README](https://github.com/ggerganov/llama.cpp/blob/master/README.md) for
instructions.

The server is compiled alongside other targets by default.

Once you have the server running, we can continue.

## Troubleshooting

### Obtaining Open-Source LLM

I recommend starting either with [llama2](https://ai.meta.com/llama/) or 
[Mistral](https://mistral.ai/). You need to download the pretrained weights
and convert them into GGUF format before they can be used with 
[llama.cpp](https://github.com/ggerganov/llama.cpp).

### Starting Server Without a GPU 

[llama.cpp](https://github.com/ggerganov/llama.cpp) supports CPU-only setups,
so you don't have to do any additional configuration. It will be slow, but
you will still have tokens generated.

### Running With a Low VRAM Memory

You can try quantization if you don't have enough VRAM on your GPU to run a 
specific model. That lowers the response quality and the memory the model
needs to use. Llama.cpp has a utility to quantize models:

```shell
$ ./quantize ./models/7B/ggml-model-f16.gguf ./models/7B/ggml-model-q4_0.gguf q4_0
```

10GB of VRAM is enough to run most quantized models.

## Starting llama.cpp Server

While writing this tutorial, I had a server started with a command:

```shell
$ ./server 
    --model ~/llama-2-7b-chat/ggml-model-q4_0.gguf 
    --n-gpu-layers 200000 
    --ctx-size 2048 
    --parallel 8 
    --cont-batching
    --mlock 
    --port 8081
```

`cont-batching` parameter is essential, because it enables 
continuous batching, which is an optimization technique that allows parallel 
request.

Without it, even with multiple `parallel` slots, the server could 
answer to only one request at a time. `cont-batching` allows the server to 
respond
to multiple completion requests in parallel.

## Configuring Resonance

All you need to do is add a configuration section that specifies the llama.cpp
server location:

```ini
[llamacpp]
host = 127.0.0.1
port = 8081
```

## Testing 

Resonance has built-in commands that connect to llama.cpp and issue requests.
You can send a sample prompt through `llamacpp:completion`:

```shell
$ php ./bin/resonance.php llamacpp:completion "How to write a 'Hello, world' in PHP?"
To write a "Hello, world" in PHP, you can use the following code:

<?php
  echo "Hello, world!";
?>

This will produce a simple "Hello, world!" message when executed.
```

## Programmatic Use

In your class, you need to use {{docs/features/dependency-injection/index}} to
inject `LlamaCppClient`:

```php
<?php

namespace App;

use Distantmagic\Resonance\LlamaCppClientInterface;
use Distantmagic\Resonance\LlamaCppCompletionRequest;

#[Singleton]
class LlamaCppGenerate 
{
    public function __construct(protected LlamaCppClientInterface $llamaCppClient) 
    {
    }

    public function doSomething(): void
    {
        $request = new LlamaCppCompletionRequest('How to make a cat happy?');

        $completion = $this->llamaCppClient->generateCompletion($request);

        // each token is a chunk of text, usually few-several letters returned
        // from the model you are using
        foreach ($completion as $token) {
            swoole_error_log(SWOOLE_LOG_DEBUG, (string) $token);
        }
    }
}
```

## Summary

In this tutorial, we went through how to start 
[llama.cpp](https://github.com/ggerganov/llama.cpp) server and connect to it 
with Resonance.
