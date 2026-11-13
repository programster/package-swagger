<?php

/*
 * Runs the PHP examples in the README and in docs/, so that they can't fall out of date with the code. These are run
 * by testing/main.php, which provides $smoke.
 *
 * The examples on a page run in order, so one can use the variables an earlier one created. An example whose last
 * statement should throw ends with a comment saying so, e.g. "// Throws InvalidValueException". It is run after the
 * page's earlier examples, and must throw that exception, and later examples don't see anything it created. The
 * examples don't need use statements, as every class in the package is imported for them. An example that starts
 * with a "// Version 3" comment shows the old API, for comparison in the upgrade guide, so it isn't run.
 */

use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$repoDir = dirname(__DIR__, 2);
$useStatements = "";

$sourceDirectory = new RecursiveDirectoryIterator($repoDir . '/src', FilesystemIterator::SKIP_DOTS);
$sourceFiles = new RecursiveIteratorIterator($sourceDirectory);

foreach ($sourceFiles as $sourceFile)
{
    $relativePath = substr($sourceFile->getPathname(), strlen($repoDir . '/src/'), -strlen('.php'));
    $useStatements .= "use Programster\\Swagger\\" . str_replace('/', '\\', $relativePath) . ";\n";
}

$runExamples = function (string $code) use ($useStatements) : void {
    ob_start();

    try
    {
        eval($useStatements . $code);
    }
    finally
    {
        ob_end_clean();
    }
};

// docs/testing.md is left out, as its examples are of the tests themselves, which use the test runner ($smoke).
$isAboutPackage = function (string $markdownFile) : bool { return basename($markdownFile) !== 'testing.md'; };
$docsPages = array_filter(glob($repoDir . '/docs/*.md'), $isAboutPackage);
$markdownFiles = array_merge([$repoDir . '/README.md'], $docsPages);

foreach ($markdownFiles as $markdownFile)
{
    $isDocsPage = basename(dirname($markdownFile)) === 'docs';
    $pageName = $isDocsPage ? 'docs/' . basename($markdownFile) : basename($markdownFile);

    $testPage = function () use ($markdownFile, $pageName, $runExamples) : void {
        preg_match_all('/^```php\n(.*?)^```/ms', file_get_contents($markdownFile), $matches);
        $passingCode = "";

        foreach ($matches[1] as $index => $example)
        {
            // The examples' own use statements (and loading of the autoloader) would clash with the ones above.
            $example = preg_replace('/^<\?php\s*/', '', $example);
            $example = preg_replace('/^(use [^;]+;|require_once[^;]+;)\n/m', '', $example);
            $exampleNumber = $index + 1;

            // An example of the old API, in the upgrade guide, isn't run.
            $isOldApi = str_starts_with($example, "// Version 3");
            $shouldThrow = preg_match('/\/\/ Throws (\w+)\s*$/', $example, $throwMatch) === 1;

            if (!$isOldApi && $shouldThrow)
            {
                // The earlier examples are run on their own first, so that one of them throwing can't be taken for
                // this one throwing.
                $runExamples($passingCode);
                $expectedClass = $throwMatch[1];
                $thrownClass = "nothing";

                try
                {
                    $runExamples($passingCode . $example);
                }
                catch (\Throwable $e)
                {
                    $thrownClass = (new \ReflectionClass($e))->getShortName();
                }

                $message = "Example {$exampleNumber} of {$pageName} should throw {$expectedClass}, but threw "
                    . "{$thrownClass}.";

                assert($thrownClass === $expectedClass, $message);
            }
            elseif (!$isOldApi)
            {
                $passingCode .= $example . "\n";
            }
        }

        $runExamples($passingCode);
    };

    $smoke->run("Docs: {$pageName}", $testPage);
}
