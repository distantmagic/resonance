<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Util\RegexHelper;

final class CommonMarkAdmonitionBlockParser extends AbstractBlockContinueParser
{
    public const BLOCK_FENCE_LENGTH = 3;

    private readonly CommonMarkAdmonitionBlock $block;

    public function __construct(string $admonitionType)
    {
        $this->block = new CommonMarkAdmonitionBlock($admonitionType);
    }

    public function canContain(AbstractBlock $childBlock): bool
    {
        return true;
    }

    public function getBlock(): CommonMarkAdmonitionBlock
    {
        return $this->block;
    }

    public function isContainer(): bool
    {
        return true;
    }

    public function tryContinue(Cursor $cursor, BlockContinueParserInterface $activeBlockParser): ?BlockContinue
    {
        if (!$cursor->isIndented() && ':' === $cursor->getNextNonSpaceCharacter()) {
            $match = RegexHelper::matchFirst('/^\:{3}$/', $cursor->getLine(), $cursor->getNextNonSpacePosition());

            if (is_array($match) && ':::' === $match[0]) {
                return BlockContinue::finished();
            }
        }

        return BlockContinue::at($cursor);
    }
}
