<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\DialogueInput\UserInput;
use Distantmagic\Resonance\DialogueMessageProducer\ConstMessageProducer;
use Distantmagic\Resonance\DialogueResponseCondition\ExactInputCondition;
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
        $responseDiscriminator = new DialogueResponseDiscriminator();

        $rootNode = new DialogueNode(
            message: new ConstMessageProducer('What is your current role?'),
            responseDiscriminator: $responseDiscriminator,
        );

        $marketingNode = new DialogueNode(
            message: new ConstMessageProducer('Hello, marketer!'),
            responseDiscriminator: $responseDiscriminator,
        );

        $rootNode->addResponse(new DialogueResponse(
            when: new LlamaCppInputCondition(
                Mockery::mock(LlamaCppClientInterface::class),
                'marketing'
            ),
            followUp: $marketingNode,
        ));

        $rootNode->addResponse(new DialogueResponse(
            when: new ExactInputCondition('marketing'),
            followUp: $marketingNode,
        ));

        $invalidNode = new DialogueNode(
            message: new ConstMessageProducer('nope :('),
            responseDiscriminator: $responseDiscriminator,
        );

        $rootNode->addResponse(new DialogueResponse(
            when: new ExactInputCondition('not_a_marketing'),
            followUp: $invalidNode,
        ));

        $response = $rootNode->respondTo(new UserInput('marketing'));

        self::assertSame($response, $marketingNode);
    }
}
