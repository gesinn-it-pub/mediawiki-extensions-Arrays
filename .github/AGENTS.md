<!-- AUTO-GENERATED from docs/gesinn-it-docs-master-pub/documents/mediawiki/instructions/ci.adoc -->

**Execution — Install Dependencies · MediaWiki**

All tests run inside a containerized MediaWiki environment managed via
[docker-compose-ci](https://github.com/gesinn-it-pub/docker-compose-ci)
(the `build/` submodule). Never run tests directly against a local PHP
or Node.js installation.

Always run `make install` before executing tests to ensure that the
latest file changes are copied into the container. Changes to source or
test files on the host are **not** automatically reflected in a running
container.

<div class="note">

When a `docker-compose.override.yml` with a bind-mount of the extension
source directory is active (local development setup), `make install` is
only required at the start of a new session or after dependency changes.
For iterative test runs, use `make php-test` or `make dev-test`
directly.

</div>

``` console
make install
```

**Execution — Run Tests (PHPUnit) · MediaWiki**

Run all PHPUnit tests:

``` console
make install composer-phpunit
```

Run a single test class or method (filtered):

``` console
make install composer-phpunit COMPOSER_PARAMS="-- --filter YourTestName"
```

Run a specific test suite:

``` console
make install composer-phpunit COMPOSER_PARAMS="-- --testsuite your-suite-name"
```

For interactive use, bash into the running container:

``` console
make bash
> composer phpunit -- --filter YourTestName
```

**Execution — Run Tests (QUnit) · MediaWiki**

Run all JavaScript tests:

``` console
make install npm-test
```

There is no direct `make` target for filtering individual tests. Bash
into the running container to run a specific test file or test case:

``` console
make bash
> npm run node-qunit -- tests/node-qunit/yourtest.test.js
```

Filter by test description:

``` console
make bash
> npx qunit --require ./tests/node-qunit/setup.js 'tests/node-qunit/**/*.test.js' --filter "your test description"
```

**Execution — Pre-Commit Gate · MediaWiki**

Before every commit, run the full CI suite to confirm nothing is broken:

``` console
make ci
```
