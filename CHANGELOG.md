# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- PHPUnit unit tests for internal helper methods (`parse_options`, `arrayUnique`, `arraySort`, `escapeForExpansion`, `isValidRegEx`, `validate_array_index`)
- GitHub Actions CI workflow via docker-compose-ci (MW 1.39 + 1.43, MySQL 8)
- Codecov badge in README

### Fixed
- `#arraysort`: replace `null` sort flag with `SORT_REGULAR` to fix PHP 8.1 deprecation and PHP 8.4 fatal error when using `nolocale` or compatibility mode
- `#arrayunique`: restore sequential array keys after deduplication ([536eec4](https://github.com/gesinn-it-pub/mediawiki-extensions-Arrays/commit/536eec4))

### Removed
- Deprecated internal method `validate_array_by_arrayId()` (had no callers)
- Redundant `VERSION` constant from `ExtArrays` class (canonical version is in `extension.json`)

## [2.2.1] — 2020-12-07

### Fixed
- Bogus `array_key_exists` check removed ([T245134](https://phabricator.wikimedia.org/T245134))

## [2.2.0]

### Changed
- Replace `Wikimedia\suppressWarnings()` / `restoreWarnings()` with `AtEase` equivalents
- Add explicit visibility modifiers to all properties and methods
- Avoid yoda conditions throughout

### Fixed
- Avoid possible frequent PHP warning in parser function handling
- Avoid PHP Notice for array definitions with an initial separator

[Unreleased]: https://github.com/gesinn-it-pub/mediawiki-extensions-Arrays/compare/3aac929...HEAD
[2.2.1]: https://github.com/wikimedia/mediawiki-extensions-Arrays/compare/774a879...3aac929
[2.2.0]: https://github.com/wikimedia/mediawiki-extensions-Arrays
