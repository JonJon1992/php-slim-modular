# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.9.7] - 2025-01-27

### Changed
- Redis connection implementation improved with separate `connectInternal()` method and authentication fix
- Scrambler enhanced with `declare(strict_types=1)`, replaced `rand()` with `random_int()` and improved code readability
- Serializable improvements: replaced `is_null()` with `=== null`, `==` with `===` and improved type validation
- Str code generation: replaced `rand()` with `random_int()` and simplified limit calculation in `code()` method
- Time validation: improved `validate()` method with explicit type conversion and renamed variables
- Routes optimization: removed unused parameter and added type hints in callbacks
- Services refactoring: replaced `==` with `===` in all comparisons and removed unused parameters

### Fixed
- Redis auth: fixed authentication call to use `$this->redis->auth()` on the correct instance
- `is_null()` usage: removed usage of `is_null()` in favor of `=== null` for better performance and compiler optimization
- Type comparisons: fixed all comparisons to use strict operator `===` instead of `==`
- Unused parameters: removed unused parameters in closures and methods

## [2.9.6] - 2025-09-26

### Added
- ValueObjects enhancement: added `getValue()` method to `IValueObject` interface and implementation in base `ValueObject` class
- Improved exception handling: better exception handling with explicit `Exception` class import

### Fixed
- Str::code() method: fixed missing parameter in `Str::code()` method for correct numeric code generation

### Changed
- ValueObject interface: `IValueObject` interface now includes `getValue()` method for direct value access
- Code generation: `Str::code()` method now works correctly with custom sizes

## [2.9.5] - 2025-08-11

### Added
- Test system and migration commands improvements

## [2.9.4] - 2025-08-07

### Added
- BaseTestCase improvements with typing and organization

## [2.9.3] - 2025-08-07

### Added
- DynamoMock implementation following RedisMock pattern

## [2.9.2] - 2025-08-06

### Added
- BaseTestCase improvements: significant improvements in the test system
- Enhanced security: secure shell commands with `escapeshellarg()` to prevent injection
- Robust error handling: comprehensive try-catch with detailed logs and validations
- Configurable permissions: support for `TEST_USER` environment variable for flexibility
- Test helpers: new methods `assertJsonResponse()`, `createTestData()`, `cleanTestData()`
- Complete documentation: detailed guide in `docs/TESTING_IMPROVEMENTS.md`
- Practical example: `examples/BaseTestCaseExample.php` file with real use cases
- Redis mock: `RedisMock` class for tests without external dependency

### Changed
- `executeCommand()` method: implemented for secure shell command execution
- SQLite configuration: improved with validations and appropriate permissions
- Debug logs: detailed logging system for operation tracking
- Validations: success verification for all shell commands
- Permissions: automatic chmod definition for SQLite files

### Fixed
- Exec commands: fixed missing spaces in `rm -f` and `mkdir -p` commands
- Security: command injection prevention with `escapeshellarg()`
- Robustness: proper error handling in critical operations
- Flexibility: support for different environments via configuration

## [2.9.1] - 2025-08-06

### Fixed
- Crypt type error: fixed "Return value must be of type string, int returned" error in `Crypt::encode()` method
- Hashids integration: forced string conversion in `encode()` method return to ensure compatibility with `ICrypt` interface
- Function conflict: fixed declaration conflict of `dd()` and `d()` functions with Symfony VarDumper
- Dependencies: installed `php8.2-bcmath` extension for complete Hashids support
- Compatibility: framework now works correctly with numeric values in encryption

### Changed
- `encode()` method: now always returns string as specified in the interface
- Debug functions: added `function_exists()` check to avoid conflicts
- Typing: ensured correct typing throughout the encryption chain

## [2.9.0] - 2025-08-05

### Changed
- Exception system: improved exception system loading order in bootstrap.php

## [2.8.9] - 2025-08-05

### Added
- Debug functions: added `dd()` and `d()` functions for Laravel-like debugging
- Compatibility: `function_exists()` check to avoid conflicts with Symfony VarDumper

## [2.8.8] - 2025-08-05

### Fixed
- Laravel conflict: fixed `env()` function conflict with Laravel/Illuminate
- Compatibility: renamed `env()` function to `getEnvVar()` in exception.php
- Redeclaration error: fixed 'Cannot redeclare env()' error in Laravel projects

## [2.8.7] - 2025-08-05

### Fixed
- Laravel conflict: fixed `env()` function conflict with Laravel/Illuminate
- Compatibility: renamed `env()` function to `getEnvVar()` to avoid conflicts
- Redeclaration error: fixed 'Cannot redeclare env()' error in Laravel projects
- Integration: framework now compatible with Laravel projects without conflicts

### Changed
- `env()` function: renamed to `getEnvVar()` to avoid conflicts
- Compatibility: better integration with other PHP frameworks

## [2.8.6] - 2025-08-05

### Fixed
- Exception handling system: fixed exception system loading in bootstrap.php
- Error messages: fixed issue where error messages didn't appear in requests
- ErrorHandler: fixed undefined `$response` property in ErrorHandler
- Debug info: improved debug information handling in development environment
- Undefined properties: removed references to non-existent properties in ErrorHandler

### Changed
- Bootstrap: added automatic exception system loading
- ErrorHandler: refactored to use already calculated error data
- Error handling: more robust and reliable system

## [2.8.5] - 2025-08-05

### Fixed
- TypeError protection: improved TypeError protection in `str_ends_with` with more robust checks

## [2.8.4] - 2025-08-05

### Fixed
- `str_ends_with` TypeError: fixed TypeError in `str_ends_with` with numeric keys

## [2.8.3] - 2025-08-05

### Changed
- Bootstrap: removed autoloader from bootstrap.php for better organization

## [2.8.2] - 2025-08-05

### Fixed
- Type conflict: fixed type conflict in `response` property of ImAliveAction

## [2.8.1] - 2025-08-05

### Fixed
- Substr issues: fixed `substr` issues with null values in AbstractAction

## [2.8.0] - 2025-08-05

### Added
- Automation scripts: system of scripts for commits and releases working perfectly

### Changed
- Version: framework updated from 2.7.0 to 2.8.0
- Automation: fully automated release process

### Fixed
- Release script: fixed logic to allow releases without pending changes

## [2.7.0] - 2025-08-05

### Changed
- Date update: all document dates updated to 2025-08-05
- Temporal consistency: CHANGELOG with standardized dates

## [2.6.0] - 2025-08-05

### Added
- Improved validation system: fixed non-existent `Awurth\Validator\Validator` dependency
- Validation with Respect\Validation: implemented validation using `Respect\Validation\Validator`
- Automation scripts: complete system of scripts for commits and releases
- Release script: `scripts/release.sh` for automated commits and releases
- Quick commit script: `scripts/quick-commit.sh` with automatic type detection
- Alias configuration: `scripts/setup-aliases.sh` to facilitate script usage

### Changed
- Method chaining fix: fixed method order in CPF validation
- Smart validation: system automatically detects commit type
- Standardization: commit messages follow project conventions

### Fixed
- Dependencies: removed non-existent validator dependency
- Validation: fixed validation logic in Value Objects

## [2.5.0] - 2025-08-05

### Changed
- Strong typing: added `declare(strict_types=1)` in all PHP files in the project
- Type fixes: type fixes in Traits, ValueObjects, Entities, Utils, Middleware, Abstract, App, Contracts, etc.
- Signature compatibility: signature compatibility between interfaces and implementations
- Docblock improvements: improved docblocks and return types
- Constructor refactoring: refactored constructors and methods for greater consistency
- PHP tag removal: removed unnecessary PHP tags in views
- Method adjustments: adjusted interface methods to ensure compatibility
- Logic fix: fixed inverted logic in collection constructors

### Fixed
- Type errors: fixed type errors and signature inconsistencies
- PHP 8.2+ compatibility: guaranteed full compatibility with PHP 8.2+
- Clean code: cleaner, safer and standardized code

## [2.4.0] - 2025-08-05

### Added
- Advanced health check: complete refactoring of `ImAliveAction` with advanced features
- DynamoDB support: DynamoDB connectivity and table verification in health check
- Detailed metrics: latency, status and service counters in health check
- Smart cache: cache system to avoid unnecessary checks
- Structured logs: success and failure logs with complete context
- Selective verification: `service` parameter to verify specific services
- Force verification: `force` parameter to ignore cache
- Strong typing: complete implementation of `declare(strict_types=1)`
- Complete documentation: detailed PHPDoc in all classes and methods
- Complete example: `examples/health-check-example.php` file with demonstrations

### Changed
- Error fix: `HEALTY_CHECK` → `HEALTHY_CHECK` (typo fix)
- Logic refactoring: separation of responsibilities into private methods
- Response structure: structured JSON with detailed information per service
- Error handling: specific exception capture with detailed messages
- HTTP status: 503 for service failures, 200 for success
- Log limit: maximum of 100 entries in log file
- Updated documentation: README.md with complete health check section

### Fixed
- Typing: added strong typing to all methods
- Organization: cleaner and more organized code
- Performance: cache reduces unnecessary checks
- Observability: detailed metrics for monitoring

## [2.3.0] - 2025-08-05

### Changed
- Dependency update: updated `jonjon1992/s3-php` from v2.1.0 to v2.4.0
- Compatibility improvements: support for new S3 PHP features
- Performance optimizations: general framework improvements

## [2.2.9] - 2025-08-05

### Added
- Documentation improvements: complete project documentation update
- Code organization: restructuring and cleanup of codebase
- Test preparation: structure prepared for unit test implementation

### Changed
- Updated version: framework updated to version 2.2.9
- Enhanced documentation: README.md and CHANGELOG.md updated

## [2.2.8] - 2025-08-05

### Added
- Stable version: framework at version 2.2.8 with all main features
- Complete AWS integration: S3, SQS and DynamoDB fully integrated
- Helper system: helpers for all main services
- Complete middleware: middleware system for authentication, CORS, rate limiting, etc.

### Changed
- Mature framework: all main features implemented and tested
- Complete documentation: README.md and CHANGELOG.md updated
- Modular structure: module system working perfectly

## [2.2.7] - 2025-08-05

### Added
- New DynamoDB helper: implemented `dynamo()` helper in `src/helpers.php` following the pattern of other helpers
- Service system integration: added support for DynamoDB configurations in `src/services.php`
- Multiple configuration support: helper supports aliases for different environments (local, production, etc.)
- Specific documentation: created `docs/utils/dynamo-helper.md` with complete helper documentation
- Usage example: updated `examples/dynamo-example.php` with helper examples
- Unit tests: created `tests/Utils/DynamoHelperTest.php` to validate the helper

### Changed
- Consistent pattern: `dynamo()` helper follows the same pattern as other helpers (redis, s3, db, etc.)
- DI integration: helper automatically integrates with dependency injection system
- Flexible configuration: support for environment variables and custom configurations

### Fixed
- Helper consistency: all helpers now follow the same implementation pattern

## [2.2.6] - 2025-08-05

### Fixed
- s3() function conflict: added `if (!function_exists('s3'))` in `src/helpers.php` to avoid function redeclaration
- Incompatible signatures: fixed incompatible signatures in `Str` class by removing `string` type hints from `endsWith()`, `camel()`, `ucfirst()` and `studly()` methods
- Package compatibility: resolved conflicts between `jonjon1992/php-slim-modular`, `jonjon1992/s3-php` and `illuminate/support` v10.48.28

### Changed
- Compatibility: improved compatibility with external dependencies

## [2.2.5] - 2025-08-05

### Added
- New DynamoDB util: implemented `Core\Utils\Dynamo` for Amazon DynamoDB integration
- Complete CRUD support: complete support for CRUD operations in DynamoDB
- Automatic configuration: automatic configuration for local environment (LocalStack) and production (AWS)
- Batch operations: batch operations support
- Queries and scans: query and scan support
- Table management: table management (create, list, describe, remove)
- Flexible conversion: flexible data conversion (objects, arrays, stdClass)
- Complete documentation: complete documentation in `docs/utils/dynamo.md`
- Usage example: usage example in `examples/dynamo-example.php`
- Integration: integration with `jonjon1992/php-dynamo` v3.0.2 package

### Changed
- Updated README: updated README.md with DynamoDB usage section
- Documentation: improved framework utils documentation

## [2.2.4] - 2025-08-05

### Fixed
- HttpException: fixed use of `HttpException` to `HttpInternalServerErrorException` in `Action.php`
- Exception classes: created custom exception classes for better error handling:
  - `AuthenticationException` for authentication errors
  - `InvalidRequestException` for request validation errors
  - `CommandException` for command execution errors
  - `AppException` for general application errors
- Specific exceptions: updated use of generic `Exception` to specific exceptions throughout the framework

### Changed
- Type safety: improved type safety and error handling

## [2.2.3] - 2025-08-05

### Fixed
- Dependency conflict: resolved additional dependency conflict with `slim/psr7` and `slim/slim`
- slim/psr7 update: updated `slim/psr7` from `1.6.1` to `^1.7` to support `psr/http-message ^2.0`
- slim/slim update: updated `slim/slim` from `4.8.1` to `^4.9` for better compatibility
- PSR conflicts: fixed all PSR HTTP Message dependency conflicts

## [2.2.2] - 2025-08-05

### Fixed
- AWS SDK conflict: resolved dependency conflict between `aws/aws-sdk-php` and `slim/php-view`
- slim/php-view update: updated `slim/php-view` from `3.1.0` to `^3.2` to support `psr/http-message ^2.0`
- AWS compatibility: fixed compatibility issue with AWS SDK PHP ^3.350

## [2.2.1] - 2025-08-05

### Changed
- S3 PHP update: updated `jonjon1992/s3-php` from `v2.0.0` to `v2.1.0`

## [2.2.0] - 2025-08-05

### Added
- AWS SDK PHP: AWS SDK PHP updated to version ^3.350
- S3 helper: `s3()` helper function to access S3 buckets
- Queue helper: `queue()` helper function to access SQS clients
- String methods: `endsWith()`, `camel()`, `ucfirst()` and `studly()` methods in `Str` class

### Fixed
- Exception without namespace: fixed use of `Exception` without namespace in multiple files
- Unused property: removed unused `$client` property in `SqsClient`
- Undefined variable: fixed undefined `$service` variable in `ImAliveAction`
- Str class import: added `Str` class import in `Trace.php`
- Endpoint validation: improved endpoint validation in `SqsClient` to avoid errors when not provided
- mime_content_type check: added check if `mime_content_type()` exists before using it in `S3.php`
- Exception without namespace: fixed all uses of `Exception` without namespace in the following files:
  - `src/helpers.php`
  - `src/Action/ImAliveAction.php`
  - `src/Commands/MigrateCreateCommand.php`
  - `src/Commands/MigrateRunCommand.php`
  - `src/Utils/Crypt.php`

### Changed
- AWS SDK PHP: AWS SDK PHP updated from `3.324.0` to `^3.350`
- SqsClient configuration: improved SqsClient configuration to be more robust
- Security checks: added security checks for optional endpoints

### Security
- Input validation: improved input data validation
- Security checks: added security checks for AWS configurations

## [2.1.0] - 2025-08-05

### Added
- Initial features: initial framework features
- Module system: module support
- Migration system: migration system
- AWS integration: AWS S3 and SQS integration
- Authentication system: authentication and encryption system
- Custom middlewares: custom middlewares
