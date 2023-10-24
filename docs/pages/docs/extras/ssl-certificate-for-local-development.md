---
collections: 
    - documents
layout: dm:document
parent: docs/extras/index
title: SSL Certificate for Local Development
description: >
    Learn how to create a self-signed SSL Certificate that can be used with 
    Swoole HTTP Server.
---

# SSL Certificate for Local Development

Some browser features require SSL to work (for example 
[SharedArrayBuffer](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/SharedArrayBuffer))
or service workers, so you might want to use self-signed SSL certificate for
local development.

## Setup

Using those five simple steps you should be able to have a usable self-signed certificate:

1. Copy `localhost.ext` and `Makefile` anywhere and put them in the same directory.
2. Adjust `SUBJ` and `PASSWD` variables in the `Makefile` to suit your needs.
3. Invoke make install as root (`sudo make install`).
4. Now it should be possible to import `/etc/ssl/certs/localhostCA.crt` into your browser as an Authority certificate (use your browser GUI).
5. You can use `/etc/ssl/certs/localhost.crt` and `/etc/ssl/private/localhost.key` in the {{docs/features/http/server}}.

## Files

```ini file:localhost.ext
authorityKeyIdentifier=keyid,issuer
basicConstraints=CA:FALSE
keyUsage = digitalSignature, nonRepudiation, keyEncipherment, dataEncipherment
subjectAltName = @alt_names
[alt_names]
DNS.1 = localhost
```

```makefile file:Makefile
PASS=yourcertificatemasterpassword
SUBJ=/C=PL/ST=MyState/L=MyLocation/O=MyOrganization/OU=MyOrganisationUnit/CN=localhost/emailAddress=admin@localhost

# Targets

localhostCA.crt: localhostCA.pem
    openssl x509 \
        -in localhostCA.pem \
        -inform PEM \
        -out localhostCA.crt

localhostCA.key:
    openssl genrsa \
        -des3 \
        -out localhostCA.key \
        -passout pass:$(PASS) \
        2048

localhostCA.pem: localhostCA.key
    openssl req \
        -x509 \
        -new \
        -nodes \
        -key localhostCA.key \
        -sha256 \
        -days 825 \
        -out localhostCA.pem \
        -passin pass:$(PASS) \
        -subj "$(SUBJ)"

localhost.key:
    openssl genrsa -out localhost.key 2048

localhost.csr: localhost.key
    openssl req \
        -new \
        -key localhost.key \
        -out localhost.csr \
        -subj "$(SUBJ)"

localhost.crt localhostCA.srl: localhost.csr localhost.ext localhostCA.pem localhostCA.key
    openssl x509 \
        -req \
        -in localhost.csr \
        -CA localhostCA.pem \
        -CAkey localhostCA.key \
        -CAcreateserial \
        -out localhost.crt \
        -days 825 \
        -sha256 \
        -passin pass:$(PASS) \
        -extfile localhost.ext

/etc/ssl/certs/localhost.crt: localhost.crt
    install localhost.crt /etc/ssl/certs/localhost.crt

/etc/ssl/certs/localhostCA.crt: localhostCA.crt
    install localhostCA.crt /etc/ssl/certs/localhostCA.crt

/etc/ssl/private/localhostCA.key: localhostCA.key
    install localhostCA.key /etc/ssl/private/localhostCA.key

/etc/ssl/private/localhost.key: localhost.key
    install localhost.key /etc/ssl/private/localhost.key

/etc/nginx/dhparam.pem: /etc/ssl/certs/localhost.crt /etc/ssl/private/localhost.key /etc/ssl/certs/localhostCA.crt /etc/ssl/private/localhostCA.key
    openssl dhparam -out /etc/nginx/dhparam.pem 4096

/usr/local/share/ca-certificates/localhostCA.crt: localhostCA.crt
    install localhostCA.crt /usr/local/share/ca-certificates/localhostCA.crt

/usr/local/share/ca-certificates/localhost.crt: localhost.crt
    install localhost.crt /usr/local/share/ca-certificates/localhost.crt

# PHONY targets

.PHONY: clean
clean:
    rm -f localhost.crt
    rm -f localhost.csr
    rm -f localhost.key
    rm -f localhostCA.crt
    rm -f localhostCA.key
    rm -f localhostCA.pem
    rm -f localhostCA.srl

.PHONY: install
install: /etc/ssl/certs/localhost.crt /etc/ssl/certs/localhostCA.crt /etc/ssl/private/localhost.key /etc/ssl/private/localhostCA.key

.PHONY: uninstall
uninstall:
    rm -f /etc/ssl/certs/localhost.crt
    rm -f /etc/ssl/certs/localhostCA.crt
    rm -f /etc/ssl/private/localhost.key
    rm -f /etc/ssl/private/localhostCA.key
    rm -f /usr/local/share/ca-certificates/localhost.crt
    rm -f /usr/local/share/ca-certificates/localhostCA.crt

.PHONY: update-ca-certificates
update-ca-certificates: /usr/local/share/ca-certificates/localhost.crt /usr/local/share/ca-certificates/localhostCA.crt
    /usr/sbin/update-ca-certificates
```
