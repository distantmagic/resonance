<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum LlmChatTemplateType: string
{
    use EnumValuesTrait;

    case ChatML = 'chatml';
    case MistralInstruct = 'mistral_instruct';
}
