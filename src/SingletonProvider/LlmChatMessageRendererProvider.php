<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\LlamaCppConfiguration;
use Distantmagic\Resonance\LlmChatMessageRenderer\ChatMLMessageRenderer;
use Distantmagic\Resonance\LlmChatMessageRenderer\MistralInstructMessageRenderer;
use Distantmagic\Resonance\LlmChatMessageRendererInterface;
use Distantmagic\Resonance\LlmChatTemplateType;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Nette\PhpGenerator\Printer;
use RuntimeException;

/**
 * @template-extends SingletonProvider<Printer>
 */
#[Singleton(provides: LlmChatMessageRendererInterface::class)]
final readonly class LlmChatMessageRendererProvider extends SingletonProvider
{
    public function __construct(
        private ChatMLMessageRenderer $chatMLMessageRenderer,
        private LlamaCppConfiguration $llamaCppConfiguration,
        private MistralInstructMessageRenderer $mistralInstructMessageRenderer,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): LlmChatMessageRendererInterface
    {
        return match ($this->llamaCppConfiguration->llmChatTemplate) {
            LlmChatTemplateType::ChatML => $this->chatMLMessageRenderer,
            LlmChatTemplateType::MistralInstruct => $this->mistralInstructMessageRenderer,
            default => throw new RuntimeException('Unsupported llm chat message format: '.$this->llamaCppConfiguration->llmChatTemplate->value),
        };
    }
}
