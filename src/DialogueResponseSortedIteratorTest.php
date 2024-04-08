<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\DialogueResponse\LiteralInputResponse;
use Distantmagic\Resonance\DialogueResponse\LlamaCppExtractSubjectResponse;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DialogueResponseSortedIterator::class)]
final class DialogueResponseSortedIteratorTest extends TestCase
{
    public function test_dialogue_responses_are_sorted_by_cost(): void
    {
        $marketingNode = DialogueNode::withMessage('Hello, marketer!');

        $response1 = new LlamaCppExtractSubjectResponse(
            llamaCppExtractSubject: Mockery::mock(LlamaCppExtractSubjectInterface::class),
            topic: "user's occupation",
            whenProvided: static function (): DialogueResponseResolution {
                return new DialogueResponseResolution(
                    followUp: null,
                    status: DialogueResponseResolutionStatus::CannotRespond,
                );
            },
        );

        $response2 = new LiteralInputResponse(
            when: 'marketing',
            followUp: $marketingNode,
        );

        $responses = [
            $response1,
            $response2,
        ];

        $sortedResponses = iterator_to_array(new DialogueResponseSortedIterator($responses));

        self::assertEquals([
            $response2,
            $response1,
        ], $sortedResponses);
    }
}
