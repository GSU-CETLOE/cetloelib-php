# cetloelib-php

Shared PHP library for CETLOE applications. One Composer package, five namespaces under `Cetloe\`:

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
