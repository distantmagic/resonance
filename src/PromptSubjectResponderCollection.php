<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\RespondsToPromptSubject;
use Ds\Map;
use Ds\Set;

readonly class PromptSubjectResponderCollection
{
    /**
     * @var Map<
     *     non-empty-string,
     *     Map<non-empty-string,PromptSubjectResponderInterface>
     * >
     */
    public Map $promptSubjectResponders;

    public function __construct()
    {
        $this->promptSubjectResponders = new Map();
    }

    public function addPromptSubjectResponder(
        RespondsToPromptSubject $respondsToLPromptSubject,
        PromptSubjectResponderInterface $promptResponder,
    ): void {
        if (!$this->promptSubjectResponders->hasKey($respondsToLPromptSubject->subject)) {
            $this->promptSubjectResponders->put($respondsToLPromptSubject->subject, new Map());
        }

        $this
            ->promptSubjectResponders
            ->get($respondsToLPromptSubject->subject)
            ->put($respondsToLPromptSubject->action, $promptResponder)
        ;
    }

    /**
     * @return Map<non-empty-string,Set<non-empty-string>>
     */
    public function getPromptableActions(): Map
    {
        /**
         * @var Map<non-empty-string,Set<non-empty-string>>
         */
        $ret = new Map();

        foreach ($this->promptSubjectResponders as $subject => $actionResponders) {
            $ret->put($subject, $actionResponders->keys());
        }

        return $ret;
    }
}
