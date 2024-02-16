<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\LlmSystemPrompt;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\LlmSystemPrompt;
use Distantmagic\Resonance\PromptSubjectResponderCollection;
use Distantmagic\Resonance\RespondsToPromptSubjectAttributeCollection;
use Ds\Set;

#[Singleton]
readonly class SubjectActionSystemPrompt extends LlmSystemPrompt
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

            $allowedActions[] = sprintf(
                'For "%s" the only allowed actions are: "%s"',
                $subject,
                $actions->join('", "'),
            );
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
        You are a natural language intepreter.
        Never ask for any clarifications.
        Always interpret user intentions to the best of your ability.
        Match user intentions the best you can with a set of predefined subjects and actions.

        Always describe everything user says for as one of the
        "{$allActionsSerialized}" actions with parameters.

        If you cannot determine the action, use "uknown".
        If you cannot determine the subject, use "uknown".
        If you are unsure, use "unknown".
        If user says something unrelated to the allowed actions, use "unknown".
        If user says something unrelated to the allowed subjects, use "unknown".

        When user asks how to do something that means they seek help.

        Summarize and repeat everyting user says using just a few words:
        - the first one being the subject ("{$subjectsSerialized}" or "unknown")
        - the second one being an action the user mentioned ("{$allActionsSerialized}" or "unknown")

        Valid combinations of subjects and actions you must adhere to:
        {$allowedActionsSerialized}

        Respond in the following format always:
        subject action parameters

        $examplesSerialized
        PROMPT;
    }

    public function getPromptContent(): string
    {
        return $this->prompt;
    }
}
