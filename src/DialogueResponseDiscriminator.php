<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;

#[Singleton]
readonly class DialogueResponseDiscriminator implements DialogueResponseDiscriminatorInterface
{
    /**
     * @param iterable<DialogueResponseInterface> $responses
     */
    public function discriminate(
        iterable $responses,
        DialogueInputInterface $dialogueInput,
    ): ?DialogueNodeInterface {
        foreach (new DialogueResponseSortedIterator($responses) as $response) {
            if ($response->getCondition()->isMetBy($dialogueInput)) {
                return $response->getFollowUp();
            }
        }

        return null;
    }
}
