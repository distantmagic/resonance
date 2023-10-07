<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;

final class CommonMarkAdmonitionBlockStartParser implements BlockStartParserInterface
{
    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart
    {
        if ($cursor->isIndented() || ':' !== $cursor->getNextNonSpaceCharacter()) {
            return BlockStart::none();
        }

        $fence = $cursor->match('/^\s*\:{'.CommonMarkAdmonitionBlockParser::BLOCK_FENCE_LENGTH.'}/');

        if (null === $fence) {
            return BlockStart::none();
        }

        $admonitionTypeRemainder = $cursor->getRemainder();
        $admonitionType = trim($admonitionTypeRemainder);

        $cursor->advanceToEnd();

        $parser = new CommonMarkAdmonitionBlockParser($admonitionType);

        return BlockStart::of($parser)->at($cursor);
    }
}
