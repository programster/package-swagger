# Testing

- [Running the tests](#running-the-tests)
- [Testing every supported PHP version](#testing-every-supported-php-version)
- [Running some of the tests](#running-some-of-the-tests)
- [Continuous integration](#continuous-integration)
- [Writing tests](#writing-tests)

## Running the tests
To run the tests on your current version of PHP, run `composer install`, and then:

```bash
php -d zend.assertions=1 testing/main.php
```

The tests are in `testing/tests/`, with one file per area of the package (e.g. `SchemasTest.php` for the schemas,
and `SecurityTest.php` for the security schemes and requirements). Between them, they create every class, call
nearly every public method, and check the JSON that each one produces. `OpenApiTest.php` also builds a whole
document and compares it against `testing/assets/users-api.json`.

A test fails if it throws, fails an `assert()`, or raises a PHP warning or deprecation. `main.php` runs every
`*Test.php` file in that folder, so a new file is picked up automatically. It exits with a non-zero code if anything
fails.

The tests check things with `assert()`, which does nothing unless `zend.assertions` is `1`. `main.php` switches it
on if it can, but most installs set it to `-1`, which can only be changed on the command line or in `php.ini`, so
`main.php` refuses to run rather than pass without checking anything.

## Testing every supported PHP version
This package supports PHP 8.1 and above. To check it works on every supported version, run the following, which uses
Docker to test each version in its own container. Use this before releasing, as it catches code that only works on
newer versions of PHP than the minimum.

```bash
testing/run-all-php-versions.sh             # test every supported version (8.1 to 8.5)
testing/run-all-php-versions.sh 8.1 8.5     # test only specific versions
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

## Continuous integration
`.github/workflows/tests.yml` runs `run-all-php-versions.sh` on pushes to `master`, every tag, and pull requests into
`master`. It can also be run on any branch by hand, with "Run workflow" on the repository's Actions tab. The README's
badge shows the result of the latest run on `master`.

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

    SmokeTest::expectException(\Exception::class, $createEmptyContact, "at least one of name, url, or email");
};

$smoke->run("Contact", $testContact);
```

`assertJson()` compares both sides in the same compact form, so the expected JSON can be laid out however is easiest
to read (e.g. in a nowdoc). The keys must be in the same order, and `{}` and `[]` are told apart, as both change the
document that is generated.

Follow the coding standards used throughout `src/` in the tests too, e.g. no arrow functions, and a closure assigned
to a descriptively named variable before it is passed to `run()` or `expectException()`.

If you change `testing/assets/users-api.json`, check that it is still a valid OpenAPI document, as described in the
README's [Validation](../README.md#validation) section.
