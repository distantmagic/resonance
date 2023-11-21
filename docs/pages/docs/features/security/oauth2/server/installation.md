---
collections: 
    - name: documents
      next: docs/features/security/oauth2/configuration
layout: dm:document
next: docs/features/security/oauth2/configuration
parent: docs/features/security/oauth2/index
title: Installation
description: >
    Learn how to setup the application environment to use OAuth 2.0.
---

# Installation

Although Resonance has built-in OAuth 2.0 support (based on 
[thephpleague/oauth2-server](https://github.com/thephpleague/oauth2-server)), 
you must take extra steps to enable the OAuth 2.0 features.

Primarily you need to generate encryption keys. To store them, create an 
`oauth2` directory in your project first.

## TL;DR

Generate public/private keypair for JWT encryption and an encryption key for
authorization and refresh codes.

## Generate Encryption Keys

### About Encryption Keys

To secure your auth data and make sure it cannot be forged, you need to 
generate a public/private key pairs (or, to be truthful, no matter the 
implementation, it can be forged, but usually the cost of forging it 
vastly outweighs whatever is to be gained by doing so - that is what in 
actuality protects our systems). 

In generating the key, you have to weigh in a few factors:
- The longer the key, the more secure it is, but the more computational power is
    required to validate data against it.
- You have to use the modern encryption algorithm.

Since 2016, 
[NIST recommends](https://cryptome.org/2016/01/CNSA-Suite-and-Quantum-Computing-FAQ.pdf) 
RSA keys of at least 3072 bit length, so we can settle for that. On the 
other hand, it might be a good idea to use keys of 4096 bit length since almost a
decade passed since that recommendation, but it's up to you.

### JWT Encryption Keys

OAuth2 uses [JWT (JSON Web Tokens)](https://datatracker.ietf.org/doc/html/rfc7519) 
as a transmission medium for the auth data like scopes, subjects, statuses, and 
such. This data is generated through the other parts of OAuth 2.0 protocol.

To encrypt it, we will need public/private key pair. You can skip `-passout` 
and `-passin` arguments if you don't want to use a passphrase with your keys.

Private key:

```shell
$ openssl genrsa -aes128 -passout pass:yourpassphrase -out oauth2/private.key 3072
```

Public key from the private key:

```shell
$ openssl rsa -in oauth2/private.key -passin pass:yourpassphrase -pubout -out oauth2/public.key
```

Then, change the CHMOD permissions for those keys to `0600`.

We will use those keys later. You should not commit them to your repo, they 
must be stored in some accessible place, but they must be accessible to the
application in some way.

The application will sign JWT tokens with a private key and expose the public 
key so the clients will be able to verify that tokens are indeed coming from 
your server (and not from some unwanted man-in-the-middle).

### Messaging Encryption Keys

Besides encrypting the JWT tokens themselves, different kind of key is 
necessary to encode refresh tokens, authorization codes and such that are not
sent as a party of JWT itself.

They use the cryptography model provided by 
[defuse/php-encryption](https://github.com/defuse/php-encryption/blob/master/docs/CryptoDetails.md).

Although it is technically possible to use 

To generate the key you have essentially two options:
1. You can provide a string pasword from which the key is slowly derived on 
    runtime (and may be vulnerable to the 
    ["long password DOS"](https://github.com/defuse/php-encryption/issues/230)),
    so you really shouldn't use this method - but it's there.
2. You can provide an already prehashed key, which is much faster.

Since I don't know why anyone would choose the option 1, I left just the option
2 in the framework. If you need the option 1 please start an issue on GitHub. 
To generate the prehashed key, use the command:

```shell
$ php ./bin/resonance.php generate:defuse-key > oauth2/defuse.key
```

Then, change the CHMOD permissions for that key to `0600`.
