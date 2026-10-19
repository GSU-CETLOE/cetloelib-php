# cetloelib-php

Shared PHP library for CETLOE applications. One Composer package, five namespaces under `Cetloe\`.

## What it does

This library is built for command-line PHP tools that read some configuration, call an API or two, and write a log. It is the plumbing those tools share, so each one is left with only its own commands.

The center of it is a bootstrap. You point `ApplicationInvoker::run()` at your application directory and namespace, and it does the rest:

1. Loads `.env` from that directory into a typed `Env` object (via Symfony Dotenv).
2. Scans `src/` and loads every class under your namespace.
3. Builds a PHP-DI container. Any class you write that extends `DefinitionSource` is picked up automatically, and sources can declare other sources they depend on.
4. Finds your commands by their `#[AsCommand]` attribute and registers them with a Symfony Console application. Commands are only instantiated when one is actually run.
5. Hands a Monolog logger to anything that implements `LoggerAwareInterface`, including every command.
6. Runs the application and exits with its status code.

A `bin/` script for a tool built on this looks like:

```php
#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

Cetloe\Console\ApplicationInvoker::run(dirname(__DIR__), 'MyTool');
```

Your commands extend `Cetloe\Console\Command\Command` instead of Symfony's. That base class logs when a command starts and finishes, and if it throws, logs the full exception chain in a stack-trace format before letting the error propagate.

Logging goes to `LOG_DIR/<app name>.<date>.log` by default, with each line stamped with the time elapsed since the process started. With no `LOG_DIR` set, it goes to stdout. `LOG_LEVEL`, `LOG_NAME`, `LOG_FILE_FORMAT`, and `LOG_FORMAT` override the rest.

For talking to HTTP APIs, extend `HttpAPIClient`. It gives you PSR-17 request building, form-encoded bodies, and a `sendRequest()` that throws a typed exception when the status code isn't one you asked for: `NotFoundException` for 404, `TooManyRequestsException` for 429, and so on. The exception carries the request and response so you can inspect them.

`Cetloe\Utils` is the odds and ends: `ArrayValue` pulls typed values out of loose arrays (handy for env vars and decoded JSON), `FileMethods` wraps the file functions that return `false` on failure so they throw instead, and `JSON` does the same for encoding and decoding.

Each piece can be used on its own. You can take the HTTP client without the console bootstrap, or the container without the logger.

The namespaces:

| Namespace | What it provides |
| --- | --- |
| `Cetloe\Container` | Extensions for [PHP-DI](https://github.com/PHP-DI/PHP-DI) and [Symfony Dotenv](https://github.com/symfony/dotenv). |
| `Cetloe\Console` | Extensions and DI bindings for [Symfony Console](https://github.com/symfony/console). |
| `Cetloe\HttpClient` | Extensions for the [Guzzle HTTP client](https://github.com/guzzle/guzzle) and DI bindings for [PSR-17](https://github.com/php-fig/http-factory) and [PSR-18](https://github.com/php-fig/http-client). |
| `Cetloe\Logger` | Extensions for [Monolog](https://github.com/Seldaek/monolog) and DI bindings for [PSR-3](https://github.com/php-fig/log). |
| `Cetloe\Utils` | Common utility classes for arrays, files, and JSON. |

## Setup

This project uses [Composer](https://getcomposer.org/) for package and script management. Install Composer by following the instructions at https://getcomposer.org/download/, then run:

```sh
composer install
```

## Using the library in a project

```sh
composer require gsu-cetloe/cetloelib-php
```

## Checks

PHPStan runs at level 9 with the strict, deprecation, and PHPUnit rule sets. PHP_CodeSniffer checks PSR-12. PHPUnit runs the test suite with warnings, notices, deprecations, and stray output all treated as failures. Run everything with:

```sh
composer check
```

Or one at a time:

```sh
composer phpcs
composer phpstan
composer test
```

Caches and scratch files from these tools land in `working/`, which is ignored by git.

## Tests

Tests live in `tests/` and mirror the `src/` layout. They use real objects throughout: a real PHP-DI container, real Monolog handlers writing to a scratch directory, and a real Symfony Application. The HTTP client tests replay canned responses through Guzzle's MockHandler so no network is involved.

`tests/Fixtures/App` is a small fixture application (commands, a definition source, a logger-aware service, and a set of shapes) that the Container, Logger, and Console tests load through the same `Env` and `ClassRepository` path a real application would use.

See [TESTPLAN.md](TESTPLAN.md) for the manual checklist.

## History

This library started as the `mjfklib` packages (console, container, httpclient, logger, utils) by Melody Joy Kimball. They were merged into a single package when the project moved to the GSU-CETLOE organization. See [LICENCE.md](LICENCE.md).
