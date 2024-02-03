<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Generator;
use http\Header;
use RuntimeException;
use Symfony\Component\Mime\Address;

/**
 * @psalm-type PPartData = array{
 *     content-description?: non-empty-string,
 *     ending-pos-body: int,
 *     starting-pos-body: int,
 * }
 */
#[Singleton]
readonly class PostfixBounceAnalyzer
{
    public function extractReport(string $deliveryReport): ?PostfixBounceReport
    {
        /**
         * @var array{
         *     diagnostic-code?: non-empty-string,
         *     original-recipient?: non-empty-string,
         *     status?: non-empty-string,
         *     x-postfix-sender?: non-empty-string,
         * } $ret
         */
        $ret = [];

        foreach ($this->parseDeliveryReport($deliveryReport) as $partData => $partBody) {
            if (!isset($partData['content-description'])) {
                continue;
            }

            if ('Notification' === $partData['content-description']) {
                $ret['notification'] = $partBody;
            }

            if ('Delivery report' === $partData['content-description']) {
                foreach ($this->parseHeaders($partBody) as $name => $value) {
                    switch ($name) {
                        case 'Original-Recipient':
                        case 'X-Postfix-Sender':
                            $ret[strtolower($name)] = $this->parseEmail($value);

                            break;
                        case 'Diagnostic-Code':
                        case 'Status':
                            $ret[strtolower($name)] = $value;

                            break;
                    }
                }
            }
        }

        /**
         * That alone is enough to notify about the bounce.
         */
        if (isset($ret['original-recipient']) && !empty($ret['original-recipient'])) {
            return new PostfixBounceReport(
                recipient: new Address($ret['original-recipient']),
                diagnosticCode: empty($ret['diagnostic-code']) ? null : $ret['diagnostic-code'],
                notification: empty($ret['notification']) ? null : $ret['notification'],
                sender: empty($ret['x-postfix-sender']) ? null : new Address($ret['x-postfix-sender']),
                status: empty($ret['status']) ? null : $ret['status'],
            );
        }

        return null;
    }

    /**
     * @return Generator<PPartData,string>
     */
    private function parseDeliveryReport(string $content): Generator
    {
        $message = mailparse_msg_create();

        try {
            if (!mailparse_msg_parse($message, $content)) {
                throw new RuntimeException('Unable to parse the email message');
            }

            $structure = mailparse_msg_get_structure($message);

            /**
             * @var string $partId
             */
            foreach ($structure as $partId) {
                $part = mailparse_msg_get_part($message, $partId);

                /**
                 * @var PPartData $partData
                 */
                $partData = mailparse_msg_get_part_data($part);

                yield $partData => trim(mb_substr(
                    $content,
                    $partData['starting-pos-body'],
                    $partData['ending-pos-body'] - $partData['starting-pos-body']
                ));
            }
        } finally {
            if (!mailparse_msg_free($message)) {
                throw new RuntimeException('Unable to free the message resource');
            }
        }
    }

    /**
     * @param non-empty-string $email
     *
     * @return non-empty-string
     */
    private function parseEmail(string $email): string
    {
        if (str_starts_with($email, 'rfc822;')) {
            $trimmed = trim(substr($email, 7));

            if (empty($trimmed)) {
                throw new RuntimeException(sprintf('Unable to parse an email: "%s"', $email));
            }

            return $trimmed;
        }

        return $email;
    }

    /**
     * @return Generator<non-empty-string,non-empty-string>
     */
    private function parseHeaders(string $content): Generator
    {
        $headers = explode("\n", $content);

        foreach ($headers as $header) {
            /**
             * @var array<non-empty-string,non-empty-string>|false $parsed
             */
            $parsed = Header::parse($header);

            if (false === $parsed) {
                continue;
            }

            foreach ($parsed as $name => $value) {
                yield $name => $value;
            }
        }
    }
}
