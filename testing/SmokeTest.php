<?php

/*
 * A small test runner for the smoke tests in testing/tests/. Each test is a named callback, which fails if it throws
 * (including a failed assert()) or raises a warning, notice or deprecation.
 */

namespace Programster\Swagger\Testing;


final class SmokeTest
{
    private readonly string $m_only;
    private int $m_numPassed = 0;
    private array $m_failures = [];
    private array $m_warnings = [];


    /**
     * Create the runner, which records any warnings, notices or deprecations raised from now on, so that the test
     * that raised them fails.
     *
     * @param string $only - only run the tests whose name contains this. An empty string runs every test.
     */
    public function __construct(string $only)
    {
        $this->m_only = $only;
        error_reporting(-1);

        $recordWarning = function (int $errorNumber, string $message, string $file, int $line) : bool {
            $this->m_warnings[] = "[E{$errorNumber}] {$message} @ " . basename($file) . ":{$line}";
            return true;
        };

        set_error_handler($recordWarning);
    }


    /**
     * Run one test, recording it as failed if it throws, or raises any warnings/deprecations.
     * @param string $label - the name of the test, shown if it fails.
     * @param \Closure $test - the test to run.
     * @return void
     */
    public function run(string $label, \Closure $test) : void
    {
        if ($this->m_only === "" || str_contains($label, $this->m_only))
        {
            $numWarningsBefore = count($this->m_warnings);
            $error = "";

            try
            {
                $test();
            }
            catch (\Throwable $e)
            {
                $location = basename($e->getFile()) . ":" . $e->getLine();
                $error = get_class($e) . ": " . $e->getMessage() . " @ " . $location;
            }

            $newWarnings = array_slice($this->m_warnings, $numWarningsBefore);

            if ($error === "" && count($newWarnings) === 0)
            {
                $this->m_numPassed++;
            }
            else
            {
                $this->m_failures[] = trim("{$label}: {$error} " . implode(" | ", $newWarnings));
            }
        }
    }


    /**
     * Assert that a callback throws a specific type of exception.
     *
     * @param string $exceptionClass - the class of exception that should be thrown.
     *
     * @param \Closure $callback - the code that should throw it.
     *
     * @param string $messageContains - optionally, text the exception's message must contain, for when the same class
     * of exception can be thrown for different reasons.
     *
     * @return void
     *
     * @throws \Throwable - if the callback throws a different exception, it is rethrown.
     * @throws \Exception - if the callback doesn't throw at all, or the message doesn't contain $messageContains.
     */
    public static function expectException(
        string $exceptionClass,
        \Closure $callback,
        string $messageContains = ""
    ) : void
    {
        $threwExpectedException = false;

        try
        {
            $callback();
        }
        catch (\Throwable $e)
        {
            if (!($e instanceof $exceptionClass))
            {
                throw $e;
            }

            if (!str_contains($e->getMessage(), $messageContains))
            {
                $actualMessage = $e->getMessage();

                throw new \Exception(
                    "Expected the {$exceptionClass} message to contain \"{$messageContains}\", but it was: " .
                    $actualMessage
                );
            }

            $threwExpectedException = true;
        }

        if (!$threwExpectedException)
        {
            throw new \Exception("Expected {$exceptionClass} to be thrown, but nothing was thrown.");
        }
    }


    /**
     * Assert that something serializes to the expected JSON. The expected JSON may be formatted however is easiest to
     * read, as both are compared in the same compact form. Keys must be in the same order, and {} and [] are told
     * apart, as both affect the document that is generated.
     *
     * @param string $expectedJson - the JSON it should serialize to.
     *
     * @param mixed $actual - the object (or value) to serialize.
     *
     * @return void
     *
     * @throws \JsonException - if $expectedJson isn't valid JSON, or $actual can't be serialized.
     */
    public static function assertJson(string $expectedJson, mixed $actual) : void
    {
        $flags = JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR;
        $expected = json_encode(json_decode($expectedJson, false, 512, JSON_THROW_ON_ERROR), $flags);
        $actualJson = json_encode($actual, $flags);
        assert($actualJson === $expected, "Expected {$expected}, but got {$actualJson}");
    }


    # Accessors
    public function getNumPassed() : int { return $this->m_numPassed; }
    public function getFailures() : array { return $this->m_failures; }
}
