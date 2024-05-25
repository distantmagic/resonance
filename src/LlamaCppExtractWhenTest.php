<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(LlamaCppExtractWhen::class)]
#[Group('llamacpp')]
final class LlamaCppExtractWhenTest extends TestCase
{
    use TestsDependencyInectionContainerTrait;

    public static function inputSubjectProvider(): Generator
    {
        yield [
            'I want to create a blog',
            'User wants to create a new application',
            YesNoMaybe::Maybe,
        ];

        yield [
            'I want to create a blog',
            'User wants to create a new feature',
            YesNoMaybe::Maybe,
        ];

        yield [
            'I want to create a comment section in my blog',
            'User wants to create a new feature',
            YesNoMaybe::Yes,
        ];

        yield [
            'make me an ATS',
            'User wants to create a new application',
            YesNoMaybe::Yes,
        ];

        yield [
            'I want to remove the ability for my users to comment on my blog',
            'User wants to delete a feature',
            YesNoMaybe::Yes,
        ];

        yield [
            'I want to create a blog',
            'User asks about a cooking recipe',
            YesNoMaybe::No,
        ];

        yield [
            'Why is my dog barking?',
            'User asks metaphysical questions',
            YesNoMaybe::No,
        ];

        yield [
            'I have no real talents and probably below average intelligence',
            'User asks if they want to pursue a career in management',
            YesNoMaybe::No,
        ];

        yield [
            'What can you do?',
            'User asks about available features',
            YesNoMaybe::Maybe,
        ];

        yield [
            'I want to do something',
            'User wants to create a new application',
            YesNoMaybe::Maybe,
        ];

        yield [
            'I have one million dollars and I dont know what to do with them',
            'User asks how to invest their money',
            YesNoMaybe::Maybe,
        ];
    }

    protected function tearDown(): void
    {
        self::$container->coroutineDriver->wait();
    }

    #[DataProvider('inputSubjectProvider')]
    public function test_llm_checks_if_user_mentiones_a_thing(
        string $input,
        string $condition,
        YesNoMaybe $expected,
    ): void {
        $llamaCppExtract = self::$container->make(LlamaCppExtractWhen::class);

        self::$container->coroutineDriver->run(static function () use ($expected, $input, $condition, $llamaCppExtract) {
            $extracted = $llamaCppExtract->extract(
                input: $input,
                condition: $condition,
            );

            self::assertFalse($extracted->isFailed);
            self::assertSame($expected, $extracted->result);
        });
    }
}
