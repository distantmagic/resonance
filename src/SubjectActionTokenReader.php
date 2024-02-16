<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use LogicException;
use Stringable;

class SubjectActionTokenReader
{
    /**
     * @var null|non-empty-string
     */
    private ?string $action = null;

    /**
     * @var null|non-empty-string
     */
    private ?string $subject = null;

    private string $unprocessedTokens = '';

    /**
     * @return null|non-empty-string
     */
    public function getAction(): ?string
    {
        return $this->action;
    }

    /**
     * @return null|non-empty-string
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function isUnknown(): bool
    {
        return 'unknown' === $this->action || 'unknown' === $this->subject;
    }

    public function write(string|Stringable $token): void
    {
        $this->unprocessedTokens .= (string) $token;

        if (is_string($this->action) && is_string($this->subject)) {
            return;
        }

        if (!str_contains($this->unprocessedTokens, ' ')) {
            return;
        }

        if (is_string($this->subject) && !is_string($this->action)) {
            $this->action = $this->readWord(1);

            return;
        }

        if (!is_string($this->action)) {
            $this->subject = $this->readWord(0);

            if ('unknown' === $this->subject) {
                $this->action = 'unknown';
            }

            return;
        }

        throw new LogicException('Both subject and action are filled');
    }

    /**
     * @return null|non-empty-string
     */
    private function readWord(int $offset): ?string
    {
        $spaceStrpos = mb_strpos($this->unprocessedTokens, ' ', $offset);

        if (false === $spaceStrpos) {
            return null;
        }

        $chunk = mb_substr($this->unprocessedTokens, $offset, $spaceStrpos - $offset);

        $this->unprocessedTokens = mb_substr($this->unprocessedTokens, $spaceStrpos + $offset);

        if (empty($chunk)) {
            return null;
        }

        return $chunk;
    }
}
