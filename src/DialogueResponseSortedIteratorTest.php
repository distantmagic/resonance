<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\DialogueMessageProducer\ConstMessageProducer;
use Distantmagic\Resonance\DialogueResponseCondition\ExactInputCondition;
use Distantmagic\Resonance\DialogueResponseCondition\LlamaCppInputCondition;
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
        $responseDiscriminator = new DialogueResponseDiscriminator();

        $marketingNode = new DialogueNode(
            message: new ConstMessageProducer('Hello, marketer!'),
            responseDiscriminator: $responseDiscriminator,
        );

        $response1 = new DialogueResponse(
            when: new LlamaCppInputCondition(
                Mockery::mock(LlamaCppClientInterface::class),
                'test'
            ),
            followUp: $marketingNode,
        );

        $response2 = new DialogueResponse(
            when: new ExactInputCondition('marketing'),
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
