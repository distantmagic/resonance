<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\DialogueInput\UserInput;
use Distantmagic\Resonance\DialogueMessageProducer\ConstMessageProducer;
use Distantmagic\Resonance\DialogueResponse\LiteralInputResponse;
use Distantmagic\Resonance\DialogueResponseCondition\LlamaCppInputCondition;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DialogueNode::class)]
final class DialogueNodeTest extends TestCase
{
    public function test_dialogue_produces_no_response(): void
    {
        $rootNode = new DialogueNode(
            message: new ConstMessageProducer('What is your current role?'),
        );

        $marketingNode = new DialogueNode(
            message: new ConstMessageProducer('Hello, marketer!'),
        );

        $rootNode->addPotentialResponse(new DialogueResponse(
            when: new LlamaCppInputCondition(
                Mockery::mock(LlamaCppClientInterface::class),
                'User states that they are working in a marketing department',
            ),
            followUp: $marketingNode,
        ));

        $rootNode->addPotentialResponse(new LiteralInputResponse(
            when: 'marketing',
            followUp: $marketingNode,
        ));

        $invalidNode = new DialogueNode(
            message: new ConstMessageProducer('nope :('),
        );

        $rootNode->addPotentialResponse(new LiteralInputResponse(
            when: 'not_a_marketing',
            followUp: $invalidNode,
        ));

        $response = $rootNode->respondTo(new UserInput('marketing'));

        self::assertSame($response, $marketingNode);
    }
}
