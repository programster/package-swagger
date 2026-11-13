# Testing

- [Running the tests](#running-the-tests)
- [Testing every supported PHP version](#testing-every-supported-php-version)
- [Running some of the tests](#running-some-of-the-tests)
- [Validating the output](#validating-the-output)
- [Continuous integration](#continuous-integration)
- [Writing tests](#writing-tests)

## Running the tests
To run the tests on your current version of PHP, run `composer install`, and then:

```bash
php -d zend.assertions=1 testing/main.php
```

The tests are in `testing/tests/`, with one file per area of the package (e.g. `SchemasTest.php` for the schemas,
and `SecurityTest.php` for the security schemes and requirements). Between them, they create every class, call
nearly every public method, check the JSON that each one produces, and check that every mistake the package should
catch throws the right exception. `OpenApiTest.php` also builds a whole document and compares it against
`testing/assets/users-api.json`.

`DocsTest.php` runs every PHP example in the README and in `docs/` (except this page), so the documentation can't
fall out of date with the code. The examples on a page run in order, so one can use what an earlier one created. An
example whose last statement should throw ends with a comment saying so, e.g. `// Throws InvalidValueException`, and
the test checks that it does. An example that starts with `// Version 3` shows the old API, in the upgrade guide,
so it isn't run.

A test fails if it throws, fails an `assert()`, or raises a PHP warning or deprecation. `main.php` runs every
`*Test.php` file in that folder, so a new file is picked up automatically. It exits with a non-zero code if anything
fails.

The tests check things with `assert()`, which does nothing unless `zend.assertions` is `1`. `main.php` switches it
on if it can, but most installs set it to `-1`, which can only be changed on the command line or in `php.ini`, so
`main.php` refuses to run rather than pass without checking anything.

## Testing every supported PHP version
This package supports PHP 8.2 and above. To check it works on every supported version, run the following, which uses
Docker to test each version in its own container. Use this before releasing, as it catches code that only works on
newer versions of PHP than the minimum.

```bash
testing/run-all-php-versions.sh             # test every supported version (8.2 to 8.5)
testing/run-all-php-versions.sh 8.2 8.5     # test only specific versions
testing/run-all-php-versions.sh --rebuild   # rebuild the Docker images first, to get the latest PHP patch releases
```

For each version, this lints every file and runs the tests. It exits with a non-zero code if anything fails. The
Docker images are built the first time they are needed, and then reused. When a new version of PHP is released, add
it to `SUPPORTED_VERSIONS` at the top of the script.

## Running some of the tests
Set `SWAGGER_TEST_ONLY` to run only the tests whose name contains it. It fails if no test matches, so a typo can't
pass without checking anything:

```bash
SWAGGER_TEST_ONLY="Parameter" php -d zend.assertions=1 testing/main.php
```

## Validating the output
The tests check that the package outputs what it should, but not that what it should output is valid OpenAPI. To
check that, run the following, which uses Docker to validate the documents with
[Redocly CLI](https://redocly.com/docs/cli/):

```bash
testing/validate-with-redocly.sh
```

It validates `testing/assets/users-api.json`, and a document that uses every feature of the package, which
`testing/validation/build-documents.php` builds, as both JSON and YAML. Redocly's recommended rules are used, with
every warning made an error, apart from two that are only advice about designing an API. They are set in
`testing/validation/redocly.yaml`. It exits with a non-zero code if any document has a problem.

When you add a feature to the package, use it in `build-documents.php`, so that its output is validated too. The
version of Redocly CLI is pinned at the top of the script, so that a new release's rules can't fail a build that
passed before.

## Continuous integration
`.github/workflows/tests.yml` runs `run-all-php-versions.sh` and then `validate-with-redocly.sh` on pushes to `master`
(and, until version 4 is released, `4.x`), every tag, and pull requests into `master`. It can also be run on any
branch by hand, with "Run workflow" on the repository's Actions tab. The README's badge shows the result of the latest
run on `master`.

## Writing tests
Add a test to the file for its area in `testing/tests/`, or create a new `*Test.php` file. `main.php` gives each file
`$smoke`, which runs a test with `$smoke->run("Name of the test", $test)`. Use `SmokeTest::assertJson()` to check the
JSON something produces, `assert()` for anything else, and `SmokeTest::expectException()` to check that something
throws:

```php
$testContact = function () : void {
    SmokeTest::assertJson('{"email":"support@example.com"}', new Contact(email: "support@example.com"));

    $createEmptyContact = function () : void {
        new Contact();
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $createEmptyContact,
        "at least one of a name, URL or email"
    );
};

$smoke->run("Contact", $testContact);
```

`assertJson()` compares both sides in the same compact form, so the expected JSON can be laid out however is easiest
to read (e.g. in a nowdoc). The keys must be in the same order, and `{}` and `[]` are told apart, as both change the
document that is generated.

Follow the coding standards used throughout `src/` in the tests too, e.g. no arrow functions, and a closure assigned
to a descriptively named variable before it is passed to `run()` or `expectException()`.

If you change `testing/assets/users-api.json`, check that it is still a valid OpenAPI document by running
`testing/validate-with-redocly.sh`.
