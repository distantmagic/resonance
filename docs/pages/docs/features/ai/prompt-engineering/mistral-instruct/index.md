---
collections: 
    - documents
layout: dm:document
parent: docs/features/ai/prompt-engineering/index
title: Mistral Instruct
description: >
    Open-Source Large Language Model with permissive licensing.
---

# Mistral Instruct

Those templates are prepared based on the offical 
[Mistral documentation](https://docs.mistral.ai/models/#chat-template).

# Usage

Check {{docs/features/ai/server/llama-cpp/index}} to see how to use those
templates with llama.cpp server.

## Chat Format

```
[INST]Instruction[/INST]
```

```php
<?php

namespace App;

use Distantmagic\Resonance\LlmPromptTemplate\MistralInstructChat;

$template = new MistralInstructChat('How to make a cat happy?');
```
