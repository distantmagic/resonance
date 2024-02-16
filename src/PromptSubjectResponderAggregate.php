<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Generator;
use Psr\Log\LoggerInterface;

#[Singleton]
readonly class PromptSubjectResponderAggregate
{
    public function __construct(
        private LoggerInterface $logger,
        private PromptSubjectResponderCollection $promptSubjectResponderCollection,
    ) {}

    public function consumeTokens(
        ?AuthenticatedUser $authenticatedUser,
        LlamaCppCompletionIterator $completion,
    ): Generator {
        $subjectActionTokenReader = new SubjectActionTokenReader();

        foreach ($completion as $token) {
            $subjectActionTokenReader->write($token);

            if ($subjectActionTokenReader->isUnknown()) {
                $completion->stop();

                break;
            }
        }

        $action = $subjectActionTokenReader->getAction();
        $subject = $subjectActionTokenReader->getSubject();

        if ($subjectActionTokenReader->isUnknown() || !isset($action, $subject)) {
            yield from $this->respondWithSubjectAction($authenticatedUser, 'unknown', 'unknown');
        } else {
            yield from $this->respondWithSubjectAction($authenticatedUser, $subject, $action);
        }
    }

    /**
     * @param non-empty-string $subject
     * @param non-empty-string $action
     */
    private function respondWithSubjectAction(
        ?AuthenticatedUser $authenticatedUser,
        string $subject,
        string $action,
    ): Generator {
        $responder = $this
            ->promptSubjectResponderCollection
            ->promptSubjectResponders
            ->get($subject, null)
            ?->get($action, null)
        ;

        if (!$responder) {
            $this->logger->warning(sprintf(
                'No prompt responder matched subject "%s" and action "%s"',
                $subject,
                $action,
            ));

            return;
        }

        $request = new PromptSubjectRequest($authenticatedUser);
        $response = new PromptSubjectResponse();

        SwooleCoroutineHelper::mustGo(static function () use ($request, $responder, $response) {
            $responder->respondToPromptSubject($request, $response);
        });

        yield from $response;
    }
}
