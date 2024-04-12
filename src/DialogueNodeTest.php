<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\DialogueInput\UserInput;
use Distantmagic\Resonance\DialogueMessageProducer\ConstMessageProducer;
use Distantmagic\Resonance\DialogueResponse\LiteralInputResponse;
use Distantmagic\Resonance\DialogueResponse\LlamaCppExtractSubjectResponse;
use Distantmagic\Resonance\DialogueResponse\LlamaCppExtractYesNoMaybeResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DialogueNode::class)]
final class DialogueNodeTest extends TestCase
{
    use TestsDependencyInectionContainerTrait;

    #[Group('llamacpp')]
    public function test_dialogue_handles_fuzzy_responses(): void
    {
        $rootNode = new DialogueNode(
            message: new ConstMessageProducer('Are you a marketer?'),
        );

        $marketingNode = new DialogueNode(
            message: new ConstMessageProducer('Hello, marketer!'),
        );

        $rootNode->addPotentialResponse(new LlamaCppExtractYesNoMaybeResponse(
            llamaCppExtractYesNoMaybe: self::$container->make(LlamaCppExtractYesNoMaybe::class),
            whenProvided: static function (LlamaCppExtractYesNoMaybeResult $response) use ($marketingNode): DialogueResponseResolution {
                return match ($response->result) {
                    YesNoMaybe::Yes => new DialogueResponseResolution(
                        followUp: $marketingNode,
                        status: DialogueResponseResolutionStatus::CanRespond,
                    ),
                    default => new DialogueResponseResolution(
                        followUp: null,
                        status: DialogueResponseResolutionStatus::CannotRespond,
                    ),
                };
            },
        ));

        $rootNode->addPotentialResponse(new LiteralInputResponse(
            when: 'marketing',
            followUp: $marketingNode,
        ));

        SwooleCoroutineHelper::mustRun(static function () use ($marketingNode, $rootNode) {
            $response = $rootNode->respondTo(new UserInput('yep'));

            self::assertFalse($response->getStatus()->isFailed());
            self::assertSame($marketingNode, $response->getFollowUp());
        });

        SwooleCoroutineHelper::mustRun(static function () use ($rootNode) {
            $response = $rootNode->respondTo(new UserInput('I do not know who I am'));

            self::assertFalse($response->getStatus()->isFailed());
            self::assertNull($response->getFollowUp());
        });
    }

    public function test_dialogue_produces_exact(): void
    {
        $rootNode = new DialogueNode(
            message: new ConstMessageProducer('What is your current role?'),
        );

        $marketingNode = new DialogueNode(
            message: new ConstMessageProducer('Hello, marketer!'),
        );

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

        self::assertFalse($response->getStatus()->isFailed());
        self::assertSame($marketingNode, $response->getFollowUp());
    }

    #[Group('llamacpp')]
    public function test_dialogue_produces_response_through_llamacpp(): void
    {
        $rootNode = new DialogueNode(
            message: new ConstMessageProducer('What is your current role?'),
        );

        $marketingNode = new DialogueNode(
            message: new ConstMessageProducer('Hello, marketer!'),
        );

        $rootNode->addPotentialResponse(new LlamaCppExtractSubjectResponse(
            llamaCppExtractSubject: self::$container->make(LlamaCppExtractSubject::class),
            topic: "user's occupation",
            whenProvided: static function (LlamaCppExtractSubjectResult $response): DialogueResponseResolution {
                return new DialogueResponseResolution(
                    followUp: new DialogueNode(
                        message: new ConstMessageProducer(sprintf('Hello, %s!', $response->content)),
                    ),
                    status: DialogueResponseResolutionStatus::CanRespond,
                );
            },
        ));

        $rootNode->addPotentialResponse(new LiteralInputResponse(
            when: 'marketing',
            followUp: $marketingNode,
        ));

        SwooleCoroutineHelper::mustRun(static function () use ($rootNode) {
            $response = $rootNode->respondTo(new UserInput('i am a recruiter'));

            self::assertFalse($response->getStatus()->isFailed());

            $followUp = $response->getFollowUp();

            self::assertNotNull($followUp);
            self::assertSame('Hello, recruiter!', (string) $followUp->getMessageProducer());
        });
    }
}
