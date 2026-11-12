<?php

/*
 * Runs the smoke tests in testing/tests/, which call (nearly) every public method in the package, and reports any that
 * fail, throw an error, or raise a warning/deprecation. There is one file of tests per area of the package, e.g.
 * SchemasTest.php. This is used by run-all-php-versions.sh to check the package works on every supported version of
 * PHP, but it can also be run directly: php testing/main.php
 * Exits with 0 if everything passed, or 1 if anything failed.
 */

require_once(__DIR__ . '/../vendor/autoload.php');

use Programster\Swagger\Testing\SmokeTest;


# The tests check things with assert(), which does nothing when zend.assertions is 0 or -1, so every test would pass
# without checking anything. It can be switched from 0 to 1 here, but -1 (the usual production setting) can only be
# changed in php.ini or on the command line.
if (ini_get('zend.assertions') !== '1' && @ini_set('zend.assertions', '1') === false)
{
    echo "zend.assertions must be 1 for the tests to check anything. Run: php -d zend.assertions=1 " . __FILE__ . "\n";
    exit(1);
}

# SWAGGER_TEST_ONLY runs only the tests whose name contains it.
$only = (string)getenv("SWAGGER_TEST_ONLY");
$smoke = new SmokeTest($only);
$testFiles = glob(__DIR__ . '/tests/*Test.php');
sort($testFiles);

foreach ($testFiles as $testFile)
{
    require $testFile;
}

$failures = $smoke->getFailures();

# With SWAGGER_TEST_ONLY, a name that matches nothing would otherwise pass without checking anything.
if ($only !== "" && $smoke->getNumPassed() === 0 && count($failures) === 0)
{
    $failures[] = "SWAGGER_TEST_ONLY: no test's name contains \"{$only}\", so nothing was run.";
}

printf("PHP %s: %d passed, %d failed\n", PHP_VERSION, $smoke->getNumPassed(), count($failures));

foreach ($failures as $failure)
{
    echo "  FAIL {$failure}\n";
}

exit(count($failures) === 0 ? 0 : 1);
