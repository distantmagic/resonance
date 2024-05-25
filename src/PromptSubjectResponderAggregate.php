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
        private CoroutineDriverInterface $coroutineDriver,
        private LoggerInterface $logger,
        private PromptSubjectResponderCollection $promptSubjectResponderCollection,
    ) {}

    /**
     * @return Generator<PromptSubjectResponseChunk>
     */
    public function createResponseFromTokens(
        ?AuthenticatedUser $authenticatedUser,
        LlamaCppCompletionIterator $completion,
        float $inactivityTimeout = DM_POOL_CONNECTION_TIMEOUT,
    ): Generator {
        $subjectActionTokenReader = new SubjectActionTokenReader();
        $prompt = '';

        foreach ($completion as $token) {
            $prompt .= (string) $token;
            $subjectActionTokenReader->write($token);

            if ($subjectActionTokenReader->isUnknown()) {
                $completion->stop();

                break;
            }

            if ($token->isFailed()) {
                yield new PromptSubjectResponseChunk(
                    isFailed: true,
                    isLastChunk: true,
                    isTimeout: false,
                    payload: '',
                );
            }
        }

        $action = $subjectActionTokenReader->getAction();
        $subject = $subjectActionTokenReader->getSubject();

        if ($subjectActionTokenReader->isEmpty()) {
            return;
        }

        if ($subjectActionTokenReader->isUnknown() || !isset($action, $subject)) {
            yield from $this->respondWithSubjectAction(
                $authenticatedUser,
                $prompt,
                'unknown',
                'unknown',
                $inactivityTimeout,
            );
        } else {
            yield from $this->respondWithSubjectAction(
                $authenticatedUser,
                $prompt,
                $subject,
                $action,
                $inactivityTimeout,
            );
        }
    }

    /**
     * @param non-empty-string $subject
     * @param non-empty-string $action
     *
     * @return Generator<PromptSubjectResponseChunk>
     */
    private function respondWithSubjectAction(
        ?AuthenticatedUser $authenticatedUser,
        string $prompt,
        string $subject,
        string $action,
        float $inactivityTimeout,
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

        $request = new PromptSubjectRequest(
            authenticatedUser: $authenticatedUser,
            prompt: $prompt,
        );
        $response = new PromptSubjectResponse($inactivityTimeout);

        $this->coroutineDriver->go(static function () use ($request, $responder, $response) {
            $responder->respondToPromptSubject($request, $response);
        });

        yield from $response;
    }
}
