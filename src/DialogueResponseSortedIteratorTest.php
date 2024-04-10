<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\DialogueResponse\LiteralInputResponse;
use Distantmagic\Resonance\DialogueResponse\LlamaCppExtractSubjectResponse;
use Distantmagic\Resonance\DialogueResponse\LlamaCppExtractWhenResponse;
use Ds\Set;
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

        $response2 = new LlamaCppExtractWhenResponse(
            llamaCppExtractWhen: Mockery::mock(LlamaCppExtractWhenInterface::class),
            condition: "user's occupation",
            whenProvided: static function (): DialogueResponseResolution {
                return new DialogueResponseResolution(
                    followUp: null,
                    status: DialogueResponseResolutionStatus::CannotRespond,
                );
            },
        );

        $response3 = new LiteralInputResponse(
            when: 'marketing',
            followUp: $marketingNode,
        );

        /**
         * @var Set<DialogueResponseInterface>
         */
        $responses = new Set([
            $response1,
            $response2,
            $response3,
        ]);

        $sortedResponses = iterator_to_array(new DialogueResponseSortedIterator($responses));

        self::assertEquals([
            $response3,
            $response1,
            $response2,
        ], $sortedResponses);
    }
}
