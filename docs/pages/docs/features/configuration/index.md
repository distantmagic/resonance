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

[database]
default[driver] = mysql
default[host] = 127.0.0.1
default[port] = 3306
default[database] = resonance
default[username] = resonance
default[password] = resonance
default[log_queries] = false
default[pool_prefill] = true
default[pool_size] = 8

[redis]
default[db_index] = 0
default[host] = 127.0.0.1
default[password] =
default[port] = 6379
default[prefix] = dm:
default[timeout] = 1
default[pool_prefill] = true
default[pool_size] = 8

[static]
base_url = https://resonance.distantmagic.com
esbuild_metafile = esbuild-meta-docs.json
input_directory = docs
output_directory = docs/build
sitemap = docs/sitemap.xml

[swoole]
host = 127.0.0.1
port = 9501
log_level = SWOOLE_LOG_DEBUG
ssl_cert_file = ssl/origin.crt
ssl_key_file = ssl/origin.key

[translator]
base_directory = app/lang
default_primary_language = en
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
[nette/schema](https://doc.nette.org/en/schema) is used for config validation:

```php
<?php

namespace App\SingletonProvider\ConfigurationProvider;

use App\ManifestConfiguration;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

/**
 * @template-extends ConfigurationProvider<ManifestConfiguration, object{
 *     background_color: string,
 *     theme_color: string,
 * }>
 */
#[Singleton(provides: ManifestConfiguration::class)]
final readonly class ManifestConfigurationProvider extends ConfigurationProvider
{
    protected function getConfigurationKey(): string
    {
        return 'manifest';
    }

    protected function getSchema(): Schema
    {
        return Expect::structure([
            'background_color' => Expect::string()->min(1)->required(),
            'theme_color' => Expect::string()->min(1)->required(),
        ]);
    }

    protected function provideConfiguration($validatedData): ManifestConfiguration
    {
        return new ManifestConfiguration(
            backgroundColor: $validatedData->background_color,
            themeColor: $validatedData->theme_color,
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
