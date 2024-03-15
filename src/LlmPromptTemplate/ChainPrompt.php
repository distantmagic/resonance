<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\LlmPromptTemplate;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\LlmPromptTemplate;
use Ds\Set;

#[Singleton]
readonly class ChainPrompt extends LlmPromptTemplate
{
    private string $prompt;

    /**
     * @var list<non-empty-string>
     */
    private array $stopWords;

    /**
     * @param array<LlmPromptTemplate> $prompts
     */
    public function __construct(array $prompts)
    {
        $gluedPrompt = '';

        /**
         * @var Set<non-empty-string>
         */
        $gluedStopWords = new Set();

        foreach ($prompts as $prompt) {
            $gluedPrompt .= $prompt->getPromptTemplateContent();

            foreach ($prompt->getStopWords() as $stopWord) {
                $gluedStopWords->add($stopWord);
            }
        }

        $this->prompt = $gluedPrompt;
        $this->stopWords = $gluedStopWords->toArray();
    }

    public function getPromptTemplateContent(): string
    {
        return $this->prompt;
    }

    public function getStopWords(): array
    {
        return $this->stopWords;
    }
}
