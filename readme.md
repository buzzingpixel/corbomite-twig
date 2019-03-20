# Corbomite Twig

Part of BuzzingPixel's Corbomite project.

Provides a very light wrapper around Twig.

## Usage

### `APP_BASE_PATH`

The `APP_BASE_PATH` constant can be defined if you'd like to set it explicitly. Otherwise. Corbomite Twig will figure it out automagically.

Use the [Corbomite Dependency Injector](https://github.com/buzzingpixel/corbomite-di) to get the Twig environment instance.

```php
<?php
declare(strict_types=1);

use corbomite\di\Di;
use corbomite\twig\TwigEnvironment;

$twig = Di::get(TwigEnvironment::class);
```

## Dev Mode

Corbomite Twig looks for an environment variable named `DEV_MODE`. If that is set to a string of `'true'`, Twig will be set to enable debugging and strict variables will be set to `true`. In addition, Twig's Debug Extension will be added, which adds the `{{ dump(var) }}` function to Twig.

## Cache Path

You can set an environment variable named `TWIG_CACHE_PATH` with a string specifying the absolute path for Twig to use for its cache. If you do not specify the cache path, it will default to `APP_BASE_PATH . '/cache'`.

## Globals

Your app or any composer package can specify arrays of globals to be added to Twig by setting the key `twigGlobalsFilePath` in composer.json `extra` object. The file specified there should return an array of `key => value` global variables.

## Twig Extensions

Your app or any composer package can specify an array of Twig Extension classes to load by setting the key `twigExtensions` in composer.json to an array of fully qualified class names in composer.json `extra` object. Those classes will be retrieved from Corbomite DI or newed up if not added to the DI config.

## Twig Template Directories

Your app or any composer package can specify an array of template directories for the Twig environment by setting the key `twigTemplatesDirectories` in composer.json to an object where the key is the namespace and the value is the absolute path to the template directory. If the key is empty it is considered the root/primary template directory. Composer packages should not have empty keys, only the application/project should.

## License

Copyright 2019 BuzzingPixel, LLC

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at [http://www.apache.org/licenses/LICENSE-2.0](http://www.apache.org/licenses/LICENSE-2.0).

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
