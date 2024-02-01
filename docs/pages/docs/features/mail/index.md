---
collections: 
    - documents
layout: dm:document
parent: docs/features/index
title: Mail
description: >
    Learn how to create and send emails with Resonance.
---

# Mail

After all those years, we still use emails. They can be surprisingly 
complicated to handle on the server side, and Resonance aims to make that 
process as simple as possible.

# Usage

## Configuration

You can configure multiple mailers. `DKIM` configuration is optional,
if you don't want to use it, skip all the `DKIM` related options.

```ini
; ...

[mailer]

aws[transport_dsn] = ses+smtp://ABC1234:abc+12/345@default

postfix[transport_dsn] = smtp://localhost
postfix[dkim_domain_name] = example.com
postfix[dkim_selector] = resonance1
postfix[dkim_signing_key_passphrase] = yourpassphrase
postfix[dkim_signing_key_private] = dkim/private.key
postfix[dkim_signing_key_public] = dkim/public.key

sendgrid[transport_dsn] = sendgrid://KEY@default

; ...
```

### DomainKeys Identified Mail (DKIM)

To use it, you must both configure `DKIM` fields in the specific mailer's
configuration (see above) and generate the signing keys. You can do so with
`openssl`:

```shell
$ openssl genrsa -aes256 -passout pass:yourpassphrase -out key.private 2048
$ openssl rsa -in key.private -pubout > key.public
```

### Transports

The transport layer is responsible for delivering the email to its destination.

Internally, Resonance bases email implementation on 
[Symfony Mailer](https://symfony.com/doc/current/mailer.html), which is 
adjusted to work seamlessly with Swoole. Resonance handles some aspects 
differently than in the original component, but you can still use
[Symfony's documentation](https://symfony.com/doc/current/mailer.html#using-built-in-transports)
for handling transports. You can also use all the 3rd party transports.

## Sending Emails

### CLI

Resonance has a built-in `mail:send` command. You can use it to send a single
email at a time, using your configuration file:

```shell
$ php bin/resonance.php mail:send \
    --from "me@example.com" \
    --to "you@example.com" \
    --subject "Hello!" \
    --transport "postfix" \
    "How is it going?"
```


### PHP

:::note
When email is enqueued in the {{docs/features/http/server}} is running, it 
won't be immediately sent instead it will be scheduled by using 
{{docs/features/swoole-server-tasks/index}}, to send the HTTP response faster 
(do not delay it by sending an email in the same thread).

It won't slow down the general email delivery time; it just won't delay sending
back the HTTP response.

The above happens by default without any further configuration.
:::

You need to use `MailerRepository` and select mailer (use the same name you
have in the configuration file) that will deliver an email:

```php
use Distantmagic\Resonance\MailerRepository;
use Symfony\Component\Mime\Email;

readonly class MySender
{
    public function __construct(
        private readonly MailerRepository $mailerRepository,
    ) {
        parent::__construct();
    }

    public function doSomething(): void 
    {
        $email = (new Email())
            ->from('me@example.com')
            ->to('you@example.com')
            ->subject('Hello!')
            ->text('How is it going?')
            ->html('How is it going?')
        ;

        //Send email through server task (if available):
        $this->mailerRepository->mailer->get('postfix')->enqueue($email);

        // Send an email immediately
        $this->mailerRepository->mailer->get('postfix')->send($email);
    }
}
```
