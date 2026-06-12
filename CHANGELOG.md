# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.2.2] - 2026-06-12

Fixes PHP 8.1/8.4 compatibility issues, restores correct `#arrayunique` key handling, and hardens internal code quality with visibility tightening and dead-code removal.

### Changed
- Tighten method visibility from public to protected in `ExtArrays` [`8b544e0`](https://github.com/gesinn-it-pub/mediawiki-extensions-Arrays/commit/8b544e0)
- Remove dead `!isset` check in `validate_array_index` [`077cfec`](https://github.com/gesinn-it-pub/mediawiki-extensions-Arrays/commit/077cfec)
- Remove deprecated internal method `validate_array_by_arrayId()` (no callers) [`af23847`](https://github.com/gesinn-it-pub/mediawiki-extensions-Arrays/commit/af23847)
- Remove redundant `VERSION` constant from `ExtArrays` class [`ba57ee9`](https://github.com/gesinn-it-pub/mediawiki-extensions-Arrays/commit/ba57ee9)
- Add Codecov badge in README [`666ecfb`](https://github.com/gesinn-it-pub/mediawiki-extensions-Arrays/commit/666ecfb)

### Fixed
- `#arraysort`: replace `null` sort flag with `SORT_REGULAR` to fix PHP 8.1 deprecation and PHP 8.4 fatal error [`a718223`](https://github.com/gesinn-it-pub/mediawiki-extensions-Arrays/commit/a718223)
- `#arrayunique`: restore sequential array keys after deduplication [`536eec4`](https://github.com/gesinn-it-pub/mediawiki-extensions-Arrays/commit/536eec4)
- Bootstrap script: exit with code 1 when run outside MediaWiki tree [`d408bee`](https://github.com/gesinn-it-pub/mediawiki-extensions-Arrays/commit/d408bee)

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

[Unreleased]: https://github.com/gesinn-it-pub/mediawiki-extensions-Arrays/compare/2.2.2...HEAD
[2.2.2]: https://github.com/gesinn-it-pub/mediawiki-extensions-Arrays/compare/3aac929...2.2.2
[2.2.1]: https://github.com/wikimedia/mediawiki-extensions-Arrays/compare/774a879...3aac929
[2.2.0]: https://github.com/wikimedia/mediawiki-extensions-Arrays
