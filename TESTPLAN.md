# Test plan

Walk this list before merging a change to cetloelib-php. The library has no runtime commands of its own; the
commands here are the Composer scripts that gate a merge.

## Automated checks

- [ ] `composer install` completes without errors on a clean checkout.
- [ ] `composer phpcs` reports no PSR-12 errors or warnings in `src/` or `tests/`.
- [ ] `composer phpstan` reports no errors at level 9 across `src/` and `tests/`.
- [ ] `composer test` passes with no warnings, notices, deprecations, or stray output.
- [ ] `composer check` runs all three in sequence and exits 0.

## Test suite coverage by namespace

Confirm each area still has tests when you touch it.

- [ ] `Cetloe\Utils`: `ArrayValue` casting and multi-name lookup, `FileMethods` file and archive helpers, `JSON` encode and decode.
- [ ] `Cetloe\Container`: `Env` loading from arguments, process environment, and `.env`; `ClassRepository` discovery and filtering; `DefinitionSource` helpers; `ContainerFactory` source chaining and precedence; `ObjectFactory`; the deprecated `Container\ArrayValue`.
- [ ] `Cetloe\Logger`: `LoggerConfig` from `Env`, `FileHandler` level and path mapping, `LoggerFactory`, `ElapsedTimeProcessor`, `LoggerAwareTrait`, and logger injection through `LoggerDefinitionSource`.
- [ ] `Cetloe\HttpClient`: status code to exception mapping, `HttpAPIClient` request building and response validation, cookie and download helpers, and the Guzzle bindings in `HttpClientDefinitionSource`.
- [ ] `Cetloe\Console`: `ApplicationFactory`, `ConsoleOutputFactory` verbosity handling, command loading through `ConsoleDefinitionSource`, the base `Command` start, finish, and error logging, and `ApplicationInvoker::invoke()`.

## Manual checks

- [ ] `working/tests` is empty after `composer test`. Leftover directories mean a test is not cleaning up.
- [ ] Running `composer test` twice in a row passes both times. Tests that mutate `$_ENV`, `$_SERVER`, or the working directory must restore them.
- [ ] A consuming application that requires `gsu-cetloe/cetloelib-php` still autoloads classes under the `Cetloe\` namespace.
