<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PostfixBounceAnalyzer::class)]
final class PostfixBounceAnalyzerTest extends TestCase
{
    public const DELIVERY_REPORT = <<<'REPORT'
    From double-bounce@myhost  Sat Feb  3 08:52:39 2024
    Return-Path: <double-bounce@myhost>
    Received: by myhost (Postfix)
        id 01862807342C; Sat,  3 Feb 2024 08:52:39 +0100 (CET)
    Date: Sat,  3 Feb 2024 08:52:39 +0100 (CET)
    From: Mail Delivery System <MAILER-DAEMON@myhost>
    Subject: Postmaster Copy: Undelivered Mail
    To: bounces@localhost
    Auto-Submitted: auto-generated
    MIME-Version: 1.0
    Content-Type: multipart/report; report-type=delivery-status;
        boundary="EC0F4807342B.1706946759/myhost"
    Content-Transfer-Encoding: 8bit
    Message-Id: <20240203075239.01862807342C@myhost>

    This is a MIME-encapsulated message.

    --EC0F4807342B.1706946759/myhost
    Content-Description: Notification
    Content-Type: text/plain; charset=utf-8
    Content-Transfer-Encoding: 8bit


    <foo@example.com>: Domain example.com does not accept mail (nullMX)

    --EC0F4807342B.1706946759/myhost
    Content-Description: Delivery report
    Content-Type: message/delivery-status

    Reporting-MTA: dns; myhost
    X-Postfix-Queue-ID: EC0F4807342B
    X-Postfix-Sender: rfc822; test@example.com
    Arrival-Date: Sat,  3 Feb 2024 08:52:38 +0100 (CET)

    Final-Recipient: rfc822; foo@example.com
    Original-Recipient: rfc822;foo@example.com
    Action: failed
    Status: 5.1.0
    Diagnostic-Code: X-Postfix; Domain example.com does not accept mail (nullMX)

    --EC0F4807342B.1706946759/myhost
    Content-Description: Undelivered Message Headers
    Content-Type: text/rfc822-headers
    Content-Transfer-Encoding: 8bit

    Return-Path: <test@example.com>
    Received: by myhost (Postfix, from userid 1026)
        id EC0F4807342B; Sat,  3 Feb 2024 08:52:38 +0100 (CET)
    From: test@example.com
    To: foo@example.com
    Subject: hello
    MIME-Version: 1.0
    Date: Sat, 03 Feb 2024 07:52:38 +0000
    Message-ID: <10b4a76ac9b4d29dfa3ca9a4cea89de8@example.com>
    DKIM-Signature: v=1; q=dns/txt; a=rsa-sha256;
     bh=def; d=mydomain;
     h=From: To: Subject: MIME-Version: Date: Message-ID; i=@mydomain;
     s=selector; t=1706946758; c=relaxed/relaxed;
     b=abc
    Content-Type: multipart/alternative; boundary=sy7-AJCQ

    --EC0F4807342B.1706946759/myhost--
    REPORT;

    public function test_delivery_report_is_analyzed(): void
    {
        $analyzer = new PostfixBounceAnalyzer();

        $report = $analyzer->extractReport(self::DELIVERY_REPORT);

        self::assertNotNull($report);
        self::assertEquals('5.1.0', $report->getStatus());
        self::assertEquals('<foo@example.com>: Domain example.com does not accept mail (nullMX)', $report->getNotification());
        self::assertEquals('foo@example.com', $report->getRecipient()->getAddress());
        self::assertEquals('test@example.com', $report->getSender()?->getAddress());
        self::assertEquals('X-Postfix; Domain example.com does not accept mail (nullMX)', $report->getDiagnosticCode());
    }
}
