<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use SplFileInfo;

readonly class PHPFileNamespaceReader
{
    public function __construct(
        private SplFileInfo $file,
    ) {}

    public function readNamespace(): ?string
    {
        $namespaceSequenceQualified = new PHPTokenSequenceMatcher([
            'T_NAMESPACE',
            'T_WHITESPACE',
            'T_NAME_QUALIFIED',
        ]);
        $namespaceSequenceString = new PHPTokenSequenceMatcher([
            'T_NAMESPACE',
            'T_WHITESPACE',
            'T_STRING',
        ]);

        foreach (new PHPFileTokenIterator($this->file) as $token) {
            $namespaceSequenceQualified->pushToken($token);
            $namespaceSequenceString->pushToken($token);

            if ($namespaceSequenceQualified->isMatching()) {
                return $namespaceSequenceQualified->matchingTokens->get(2)->text;
            }
            if ($namespaceSequenceString->isMatching()) {
                return $namespaceSequenceString->matchingTokens->get(2)->text;
            }
        }

        return null;
    }
}
