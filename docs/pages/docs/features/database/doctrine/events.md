---
collections: 
    - documents
layout: dm:document
parent: docs/features/database/doctrine/index
title: Events
description: >
    Learn how to hook into Doctrine's event system.
---

# Events

You can hook into Doctrine's lifecycle events, including global events and 
events specific to a particular entity.

# Usage

## Global Doctrine Events

Needs `ListensToDoctrineEvents` attribute. For example:

```php
<?php

namespace App\DoctrineEventSubscriber;

use Distantmagic\Resonance\Attribute\ListensToDoctrineEvents;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\DoctrineEventSubscriber;
use Distantmagic\Resonance\SingletonCollection;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;

#[ListensToDoctrineEvents]
#[Singleton(collection: SingletonCollection::DoctrineEventListener)]
readonly class AugumentClassMetadata extends DoctrineEventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        // augument class metada with something...
    }
}
```

## Entity Listeners

Resonance provides `ListensToDoctrineEntityEvents` attribute that binds the
listener to the doctrine entity.

Such a class has to be a singleton, added to the `DoctrineEntityListener` 
collection.

After marking a class with the above attribute, you can add it's methods 
in a way that is described in 
[Doctrine's documentation](https://www.doctrine-project.org/projects/doctrine-orm/en/3.0/reference/events.html#entity-listeners-class).

```php
<?php

declare(strict_types=1);

namespace App\DoctrineEntityListener;

use App\DoctrineEntity\MyEntity;
use Distantmagic\Resonance\Attribute\ListensToDoctrineEntityEvents;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\DoctrineEntityListener;
use Distantmagic\Resonance\Environment;
use Distantmagic\Resonance\SingletonCollection;

#[ListensToDoctrineEntityEvents(MyEntity::class)]
#[Singleton(collection: SingletonCollection::DoctrineEntityListener)]
readonly class SendNewsletterDoubleOptinMail extends DoctrineEntityListener
{
    public function postPersist(MyEntity $myEntity): void
    {
        // do something with $myEntity...
    }
}
```
