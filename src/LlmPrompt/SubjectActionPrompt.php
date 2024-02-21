<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\LlmPrompt;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\LlmPrompt;
use Distantmagic\Resonance\PromptSubjectResponderCollection;
use Distantmagic\Resonance\RespondsToPromptSubjectAttributeCollection;
use Ds\Set;

#[Singleton]
readonly class SubjectActionPrompt extends LlmPrompt
{
    /**
     * @var non-empty-string $prompt
     */
    private string $prompt;

    public function __construct(
        PromptSubjectResponderCollection $promptSubjectResponderCollection,
        RespondsToPromptSubjectAttributeCollection $respondsToPromptSubjectAttributeCollection,
    ) {
        /**
         * @var Set<non-empty-string>
         */
        $allActions = new Set();

        /**
         * @var array<non-empty-string>
         */
        $subjects = [];

        /**
         * @var array<non-empty-string>
         */
        $allowedActions = [];

        foreach ($promptSubjectResponderCollection->getPromptableActions() as $subject => $actions) {
            $subjects[] = $subject;
            $allActions = $allActions->merge($actions);

            foreach ($actions as $action) {
                $allowedActions[] = sprintf('"%s" "%s"', $subject, $action);
            }
        }

        $allActionsSerialized = implode('", "', $allActions->toArray());
        $subjectsSerialized = implode('", "', $subjects);
        $allowedActionsSerialized = '- '.implode("\n -", $allowedActions);

        /**
         * @var array<non-empty-string>
         */
        $examples = [];

        foreach ($respondsToPromptSubjectAttributeCollection->attributes as $attribute) {
            foreach ($attribute->examples as $example) {
                $examples[] = sprintf(
                    'When user says "%s" then the correct response is "%s %s"',
                    $example,
                    $attribute->subject,
                    $attribute->action,
                );
            }
        }

        if (empty($examples)) {
            $examplesSerialized = '';
        } else {
            $examplesSerialized = "Examples:\n- ".implode("\n -", $examples);
        }

        $this->prompt = <<<PROMPT
        Summarize and repeat everyting user says using just two words:
        - the first word being the subject ("{$subjectsSerialized}" or "unknown")
        - the second word being an action the user mentioned ("{$allActionsSerialized}" or "unknown")

        You must only use the following subject and category combinations:
        {$allowedActionsSerialized}

        Respond in the following format always:
        subject action

        {$examplesSerialized}
        PROMPT;
    }

    public function getPromptContent(): string
    {
        return $this->prompt;
    }
}
