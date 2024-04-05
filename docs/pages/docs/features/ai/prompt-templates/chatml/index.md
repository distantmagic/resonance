---
collections: 
    - documents
layout: dm:document
parent: docs/features/ai/prompt-templates/index
title: ChatML
description: >
    Chat Markup Language used both by OpenAI and some Open-Source models 
    like Hermes
---

# ChatML

Those templates are prepared based on the 
[Hermes 2 documentation](https://huggingface.co/NousResearch/Hermes-2-Pro-Mistral-7B-GGUF)
and
[OpenAI documentation](https://github.com/openai/openai-python/blob/release-v0.28.0/chatml.md).

# Usage

## Chat Format

```
<|im_start|>actor
prompt<|im_end|>
```

```php
<?php

namespace App;

use Distantmagic\Resonance\LlmPromptTemplate\GennaInstructChat;

$template = new GennaInstructChat('user', 'How to make a cat happy?');
```
