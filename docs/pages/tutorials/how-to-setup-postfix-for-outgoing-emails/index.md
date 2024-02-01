---
collections:
  - tutorials
layout: dm:tutorial
parent: tutorials/index
title: How to Setup Postfix for Outgoing Emails
description: >
    Learn how to set up an outgoing email server (no inbox) with Postfix and
    send transactional emails with Resonance.
---

## Assumptions

You are using 3rd party email service provider (like Gmail, Outlook, 
ProtonMail, iCloud, etc), you have a server capable of running Postfix and
you want to have the capability to send transactional emails while preserving
your 3rd party service for incoming emails.

## No Guarantees

It takes a lot of work to set up a deliverable email server. For me, the setup 
I described here works perfectly - my emails weren't blacklisted or 
automatically classified as spam. I was able to deliver them to all major
email service providers successfully.

Remember that your results may vary; many factors contribute to
deliverability, and I'm not responsible if something goes wrong. You have been
warned.

## The Goal

Our final goal is to be able to send transactional emails from our server and 
still be able to both receive and send them through 3rd party service provider. 
Let me explain.

Postfix is an open-source email transfer agent. It can act both as an SMTP
server and client, but we will only use its server capabilities.
When it comes to the client part, you can use any email service 
provider.

Setting up an email server is unreasonably burdensome because you must handle 
the incoming spam and storage. It's much easier to rely on an external
email provider. On the other hand, free email providers are unsuitable for 
sending transactional emails.

What if we could combine the best parts of both worlds and be able to send 
transactional emails through our server and receive them through a 3rd party 
client?

## Postfix

### Installation

There are multiple ways to install Postfix, depending on your operating system 
so that I won't be covering that here. I'll assume you have it running.

It's pretty simple; for example, on a Debian-based system, it's usually just a 
matter of:

```shell
$ sudo apt install Postfix
```

### Configuration

To deliver your emails to most inboxes, you need to enable TLS
email encryption in your Postfix server. To do so, you need to add the lines:

```
smtpd_tls_security_level=encrypt
smtpd_tls_loglevel = 1

smtp_tls_security_level=encrypt
smtp_tls_loglevel = 1
```

`*_loglevel` setting is optional to add; it just helps with debugging potential
issues. 

Also, you need to make sure that `myhostname` variable is set accurately to 
either the current server IP or its reverse DNS address. That is crucial for 
setting up `SPF`.

## DNS Setup

### DKIM Record

To make your emails deliverable, you need to create a `DKIM` record for your 
server (see {{docs/features/mail/index}} to learn how to generate the 
encryption keys):

```
Type:  TXT
Name:  YOURDKIMSELECTOR._domainkey
Value: v=DKIM1; k=rsa; p=YOURPUBLICKEY
```

`YOURDKIMSELECTOR` will be used when sending emails to tell the recipient
to validate them against the server we are currently setting up.

`YOURPUBLICKEY` is the public key with newlines and the envelope removed 
(without the `-----BEGIN PUBLIC KEY-----` and `-----END PUBLIC KEY-----` 
parts - or similar). Your Resonance application will have to use the same 
key pair you are using here to encrypt emails.

### DMARC Record

You most likely already have DMARC record configured, as most 3rd party email
providers require it. It should point to an email that handles potential
deliverability issues. For example:

```
Type:  TXT
Name:  _dmarc
Value: v=DMARC1; p=none; rua=mailto:postmaster@YOURDOMAIN
```

Replace `YOURDOMAIN` with your actual domain.

### SPF Record

SPF records tell what servers can send emails from your domain.

You probably already have a configured SPF record from your email 
provider. For example, if you are using [Protonmail](https://protonmail.com/)
you might have a record like this:

```
Type:  TXT
Name:  @
Value: v=spf1 include:_spf.protonmail.ch mx ~all
```

You need to change it to incorporate your server's IP address (you can also
delegate that to another record by using `include:`). For example:

```
Type:  TXT
Name:  @
Value: v=spf1 include:_spf.protonmail.ch ip4:YOURSERVERIP mx ~all
```

Replace `YOURSERVERIP` with your actual server IP address.

By setting up the SPF record like above, you will be able to use both your
email provider and your server to send emails.

## Resonance Setup

Once you have both Postfix and DNS records set up, you can configure Resonance
to handle outgoing emails through Postfix.

### Configuration

Add Postfix section to your mailer (you can name it any way you want; it 
doesn't have to be `postfix`):

```ini file:config.ini
; ...

[mailer]
postfix[transport_dsn] = native://default
postfix[dkim_domain_name] = YOURDOMAIN
postfix[dkim_selector] = YOURDKIMSELECTOR
postfix[dkim_signing_key_passphrase] = YOURPASSPHRASE
postfix[dkim_signing_key_private] = dkim/private.key
postfix[dkim_signing_key_public] = dkim/public.key

; ...
```

Replace `YOURDOMAIN`, `YOURDKIMSELECTOR`, `YOURPASSPHRASE` with appropriate
values.

### Sending Emails

You can refer to {{docs/features/mail/index}} documentation to learn how to 
send emails with Resonance.

You should be able to immediately send a deliverable email by using the command 
line:

```shell
$ php bin/resonance.php mail:send \
    --from "me@YOURDOMAIN" \
    --to "you@YOURDOMAIN" \
    --subject "Hello!" \
    --transport "postfix" \
    "How is it going?"
```

Or programmatically:

```php
/**
 * @var Distantmagic\Resonance\MailerRepository $mailerRepository
 * @var Symfony\Component\Mime\Email $email
 */
$mailerRepository->mailer->get('postfix')->enqueue($email);
```

Remember to warm up your IP address and domain before lots of transactional 
emails.

## Summary

By now, you should have a transactional email server with its own dedicated
IP address and still retain the possibility of receiving incoming emails in the
same inbox you have used and loved. :) Congratulations! 
