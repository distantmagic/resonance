<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\DialogueMessageProducer;

use Distantmagic\Resonance\DialogueMessageChunk;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ConstMessageProducer::class)]
final class ConstMessageProducerTest extends TestCase
{
    public function test_message_is_produced(): void
    {
        $inputMessage = 'What is your current role?';
        $messageProducer = new ConstMessageProducer($inputMessage);

        $message = '';

        foreach ($messageProducer as $messageChunk) {
            self::assertInstanceOf(DialogueMessageChunk::class, $messageChunk);
            self::assertFalse($messageChunk->isFailed);
            self::assertTrue($messageChunk->isLastToken);

            $message .= $messageChunk->content;
        }

        self::assertSame($inputMessage, $message);
    }
}
