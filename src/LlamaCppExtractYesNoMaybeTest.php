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
#[CoversClass(LlamaCppExtractYesNoMaybe::class)]
#[Group('llamacpp')]
final class LlamaCppExtractYesNoMaybeTest extends TestCase
{
    use TestsDependencyInectionContainerTrait;

    public static function inputSubjectProvider(): Generator
    {
        yield [
            'I CONFIRM',
            YesNoMaybe::Yes,
        ];

        yield [
            'yes',
            YesNoMaybe::Yes,
        ];

        yield [
            'i do not want that',
            YesNoMaybe::No,
        ];

        yield [
            'no',
            YesNoMaybe::No,
        ];

        yield [
            'i dont know',
            YesNoMaybe::Maybe,
        ];

        yield [
            'maybe',
            YesNoMaybe::Maybe,
        ];
    }

    protected function tearDown(): void
    {
        self::$container->coroutineDriver->wait();
    }

    #[DataProvider('inputSubjectProvider')]
    public function test_application_name_is_provided(string $input, YesNoMaybe $expected): void
    {
        $llamaCppExtract = self::$container->make(LlamaCppExtractYesNoMaybe::class);

        self::$container->coroutineDriver->run(static function () use ($expected, $input, $llamaCppExtract) {
            $extracted = $llamaCppExtract->extract(input: $input);

            self::assertSame($expected, $extracted->result);
        });
    }
}
