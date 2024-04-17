---
collections: 
    - documents
layout: dm:document
parent: docs/features/ai/index
title: Prompt Subject Responders
description: >
    Handle HTTP requests with asynchronous HTTP Responders. 
---

# Prompt Subject Responders

:::note
At the moment this feature requires {{docs/features/ai/server/llama-cpp/index}}.

In the future releases we plan to support [Ollama](https://ollama.com/).
:::

This feature allows to implement a convenient LLM Chat Controllers that can
provide responses to LLM prompts on specific topics. 

It automatically prepares both the system prompt and
[Backus–Naur form](https://en.wikipedia.org/wiki/Backus%E2%80%93Naur_form) 
grammar that are forwarded to the LLM through 
{{docs/features/ai/server/llama-cpp/index}}.

## What Is It Good For?

You can use Prompt Subject Responders to execute hard-coded tasks.

For example a good use-case would be an admin panel done through a 
conversational interface.

You do not have to create a separate admin panel views for your features, you 
can register responders instead - they will trigger when user mentions specific
phrases or actions in the chat.

# Usage

Let's define the following LLM prompt controller:

```php
<?php

namespace App\PromptSubjectResponder;

use Distantmagic\Resonance\Attribute\RespondsToPromptSubject;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PromptSubjectRequest;
use Distantmagic\Resonance\PromptSubjectResponderInterface;
use Distantmagic\Resonance\PromptSubjectResponse;
use Distantmagic\Resonance\SingletonCollection;

#[RespondsToPromptSubject(
    action: 'adopt',
    subject: 'cat',
)]
#[Singleton(collection: SingletonCollection::PromptSubjectResponder)]
readonly class CatAdopt implements PromptSubjectResponderInterface
{
    public function respondToPromptSubject(PromptSubjectRequest $request, PromptSubjectResponse $response): void
    {
        $response->write("Here you go:\n\n");
        $response->write("   |\_._/|\n");
        $response->write("   | o o |\n");
        $response->write("   (  T  )\n");
        $response->write("  .^`-^-`^.\n");
        $response->write("  `.  ;  .`\n");
        $response->write("  | | | | |\n");
        $response->write(" ((_((|))_))\n");
        $response->end();
    }
}
```

Each time user mentions something like:
- `I want to adopt a cat`
- `Give me some kittens please`
- etc

That questions will be forwarded to the above controller. All you need to do is
to specify the `action` and `subject` parameters.
