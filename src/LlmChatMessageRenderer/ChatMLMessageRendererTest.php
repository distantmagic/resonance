<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\LlmChatMessageRenderer;

use Distantmagic\Resonance\LlmChatMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ChatMLMessageRenderer::class)]
final class ChatMLMessageRendererTest extends TestCase
{
    public function test_chatml_message_is_rendered(): void
    {
        $chatMessageRenderer = new ChatMLMessageRenderer();

        self::assertSame(
            <<<'EXPECTED_MESSAGE'
            <|im_start|>system
            How can I help?<|im_end|>
            EXPECTED_MESSAGE,
            $chatMessageRenderer->renderLlmChatMessage(new LlmChatMessage(
                actor: 'system',
                message: 'How can I help?',
            ))
        );
    }
}
