<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(SubjectActionTokenReader::class)]
final class SubjectActionTokenReaderTest extends TestCase
{
    public function test_reads_subject_and_action(): void
    {
        $reader = new SubjectActionTokenReader();

        self::assertNull($reader->getAction());
        self::assertNull($reader->getSubject());
        self::assertFalse($reader->isUnknown());

        $reader->write('blog_post');

        self::assertNull($reader->getAction());
        self::assertNull($reader->getSubject());
        self::assertFalse($reader->isUnknown());

        $reader->write(' cre');

        self::assertNull($reader->getAction());
        self::assertSame('blog_post', $reader->getSubject());
        self::assertFalse($reader->isUnknown());

        $reader->write('ate');

        self::assertNull($reader->getAction());
        self::assertSame('blog_post', $reader->getSubject());
        self::assertFalse($reader->isUnknown());

        $reader->write(' ');

        self::assertSame('create', $reader->getAction());
        self::assertSame('blog_post', $reader->getSubject());
        self::assertFalse($reader->isUnknown());
    }

    public function test_reads_unknown_action(): void
    {
        $reader = new SubjectActionTokenReader();

        self::assertNull($reader->getAction());
        self::assertNull($reader->getSubject());
        self::assertFalse($reader->isUnknown());

        $reader->write('blog_post ');

        self::assertNull($reader->getAction());
        self::assertSame('blog_post', $reader->getSubject());
        self::assertFalse($reader->isUnknown());

        $reader->write('unknown ');

        self::assertSame('unknown', $reader->getAction());
        self::assertSame('blog_post', $reader->getSubject());
        self::assertTrue($reader->isUnknown());
    }

    public function test_reads_unknown_subject(): void
    {
        $reader = new SubjectActionTokenReader();

        self::assertNull($reader->getAction());
        self::assertNull($reader->getSubject());
        self::assertFalse($reader->isUnknown());

        $reader->write('unknown');

        self::assertNull($reader->getAction());
        self::assertNull($reader->getSubject());
        self::assertFalse($reader->isUnknown());

        $reader->write(' ');

        self::assertSame('unknown', $reader->getAction());
        self::assertSame('unknown', $reader->getSubject());
        self::assertTrue($reader->isUnknown());
    }
}
