---
collections: 
    - documents
layout: dm:document
parent: docs/features/index
title: Configuration
description: >
    Use the configuration module to set up the framework's built-in features. 
    Add and validate your configuration sections.
---

# Configuration

## System Configuration

Before loading Resonance, your application should set a few PHP constants, 
preferably in the autoloaded file. Those constants are more system-related than 
application-related. They define hard-coded values that should not be changed 
in the app configuration maliciously or by accident.

The application loads those constants before it parses any other configuration 
file.

Those are the required constants:

```php file:constants.php
<?php

// Root directory of your application
define('DM_APP_ROOT', __DIR__.'/app');
// Root directory of the Resonance framework
define('DM_RESONANCE_ROOT', __DIR__.'/vendor/distantmagic/resonance');
// Directory with publicly accessible files (like assets, images etc)
define('DM_PUBLIC_ROOT', __DIR__.'/public');
// Project root directory
define('DM_ROOT', __DIR__);
// Coroutine timeouts
define('DM_BATCH_PROMISE_TIMEOUT', 0.3);
define('DM_GRAPHQL_PROMISE_TIMEOUT', 0.2);
define('DM_POOL_CONNECTION_TIMEOUT', 0.1);
```

```json file:composer.json
{
    "require": {},
    "autoload": {
        "files": [
            "constants.php"
        ]
    }
}
```

You should never use `constants.php` to handle application-specific (domain
specific) configuration variables (like the database connection parameters
or such).

## Application Configuration

You can configure  Resonance's services by editing the `config.ini` file.
Each section contains a configuration for a specific service. 

### Default Configuration

Sections are lazy-loaded and will only be loaded and validated if your code 
uses a specific feature. For example, if your application does not use 
{{docs/features/http/sessions}} (and does not use Redis anywhere else), then 
the `[redis]` section won't be loaded.

This is the default configuration file:

```ini file:config.ini
[app]
env = development
esbuild_metafile = esbuild-meta-app.json
scheme = https
url = https://resonance.distantmagic.com

[database]
default[driver] = mysql
default[host] = 127.0.0.1
default[port] = 3306
default[database] = distantmagic
default[username] = distantmagic
default[password] = distantmagic
default[log_queries] = false
default[pool_prefill] = false
default[pool_size] = 8

[llamacpp]
host = 127.0.0.1
port = 8081

[mailer]
default[transport_dsn] = smtp://localhost
default[dkim_domain_name] = example.com
default[dkim_selector] = resonance1
default[dkim_signing_key_passphrase] = yourpassphrase
default[dkim_signing_key_private] = dkim/private.key
default[dkim_signing_key_public] = dkim/public.key

[manifest]
background_color = "#ffffff"
theme_color = "#ffffff"

[oauth2]
encryption_key = oauth2/defuse.key
jwt_signing_key_passphrase = yourpassphrase
jwt_signing_key_private = oauth2/private.key
jwt_signing_key_public = oauth2/public.key
session_key_authorization_request = oauth2.authorization_request
session_key_pkce = oauth2.pkce
session_key_state = oauth2.state

[openapi]
description = description
title = title
version = 0.0.0

[redis]
default[db_index] = 0
default[host] = 127.0.0.1
default[password] =
default[port] = 6379
default[prefix] = dm:
default[timeout] = 1
default[pool_prefill] = false
default[pool_size] = 8

[session]
cookie_lifespan = 86400
cookie_name = dmsession
redis_connection_pool = default

[sqlite-vss]
extension_vector0 = vector0.so
extension_vss0 = vss0.so

[static]
base_url = https://resonance.distantmagic.com
esbuild_metafile = esbuild-meta-docs.json
input_directory = docs
output_directory = docs/build
sitemap = docs/build/sitemap.xml

[swoole]
host = 127.0.0.1
port = 9501
log_level = SWOOLE_LOG_DEBUG
ssl_cert_file = ssl/origin.crt
ssl_key_file = ssl/origin.key

[translator]
base_directory = app/lang
default_primary_language = en

[websocket]
max_connections = 10000
```

### Environment Variables

You can follow the [parse_ini_file](https://www.php.net/manual/en/function.parse-ini-file.php)
interpolation rules. For example:

```ini file:config.ini
; Interpolate the PATH environment variable
path = ${PATH}
```

### Configuration Providers

You can extend `config.ini` by adding your configuration sections. Let's say 
you want to add a new `[manifest]` section that configures some values that 
the [Web App Manifest](https://developer.mozilla.org/en-US/docs/Web/Manifest) 
will use.

For your config file to be able to have a section like this:

```ini file:config.ini
; ...

[manifest]
background_color = "#000000"
theme_color = "#000000"

; ...
```

First, you need to define your configuration model:

```php
<?php

namespace App;

readonly class ManifestConfiguration
{
    public function __construct(
        public string $backgroundColor,
        public string $themeColor,
    ) {}
}
```

Then you need to define the configuration provider. 
Constraints schema is used for config validation:

```php
<?php

namespace App\SingletonProvider\ConfigurationProvider;

use App\ManifestConfiguration;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\Constraint\ObjectConstraint;
use Distantmagic\Resonance\Constraint\StringConstraint;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

/**
 * @template-extends ConfigurationProvider<ManifestConfiguration, array{
 *     background_color: string,
 *     theme_color: string,
 * }>
 */
#[Singleton(provides: ManifestConfiguration::class)]
final readonly class ManifestConfigurationProvider extends ConfigurationProvider
{
    public function getConstraint(): Constraint
    {
        return new ObjectConstraint(
            properties: [
                'background_color' => new StringConstraint(),
                'theme_color' => new StringConstraint(),
            ],
        );
    }

    protected function getConfigurationKey(): string
    {
        return 'manifest';
    }

    protected function provideConfiguration($validatedData): ManifestConfiguration
    {
        return new ManifestConfiguration(
            backgroundColor: $validatedData['background_color'],
            themeColor: $validatedData['theme_color'],
        );
    }
}
```

Then you can use your configuration file in other services, for example:

```php
<?php

namespace App\HttpResponder;

use App\HttpRouteSymbol;
use App\ManifestConfiguration;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\ContentType;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\RequestMethod;
use Distantmagic\Resonance\SingletonCollection;
use Swoole\Http\Request;
use Swoole\Http\Response;

#[RespondsToHttp(
    method: RequestMethod::GET,
    pattern: '/manifest.webmanifest',
    routeSymbol: HttpRouteSymbol::Manifest,
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
final readonly class Manifest extends HttpResponder
{
    private string $manifest;

    public function __construct(ManifestConfiguration $manifestConfiguration) 
    {
        $this->manifest = json_encode([
            'background_color' => $manifestConfiguration->backgroundColor,
            'display' => 'standalone',
            'id' => '/?source=pwa',
            'name' => 'Resonance',
            'scope' => '/',
            'short_name' => 'Resonance',
            'start_url' => '/?source=pwa',
            'theme_color' => $manifestConfiguration->themeColor,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function respond(Request $request, Response $response): ?HttpResponderInterface
    {
        $response->header('content-type', ContentType::ApplicationJson->value);
        $response->end($this->manifest);

        return null;
    }
}
```

This configuration is going to be loaded only if the `ManifestConfiguration`
is used as a constructor parameter in a Singleton. See more at 
{{docs/features/dependency-injection/index}}.
