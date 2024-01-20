---
collections: 
    - documents
draft: true
layout: dm:document
parent: docs/features/ai/prompt-engineering/index
title: Phi-2
description: >
    Small Language Model from Microsoft. Memory efficient, performant, 
    unreliable.
---

# Phi-2

Those templates are prepared based on the offical 
[Phi-2 documentation](https://huggingface.co/microsoft/phi-2).

# Usage

## QA Format

It is crucial to add the `Question:` and `Answer:` strings to the prompt, 
without them Phi-2 tends to stray off the question a lot.

This format is great for one-off questions.

```
Question: How to make a cat happy?
Answer:
```

## Chat Format

```
Alice: I don't know why, I'm struggling to maintain focus while studying. Any suggestions?
Bob: Well, have you tried creating a study schedule and sticking to it?
Alice: Yes, I have, but it doesn't seem to help much.
Bob: Hmm, maybe you should try studying in a quiet environment, like the library.
Alice: ...
```

## Code Format

Phi-2 can generate some basic Python code.
