---
collections: 
    - documents
layout: dm:document
parent: docs/features/ai/prompt-templates/index
title: Gemma
description: >
    Google's Open-Source alternative to Gemini.
---

# Gemma

Those templates are prepared based on the 
[Huggingsface blog release](https://huggingface.co/blog/gemma#prompt-format).

# Usage

## Chat Format

```
<start_of_turn>user
knock knock<end_of_turn>
```

```php
<?php

namespace App;

use Distantmagic\Resonance\LlmPromptTemplate\GemmaInstructChat;

$template = new GemmaInstructChat('user', 'How to make a cat happy?');
```
