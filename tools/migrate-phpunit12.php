#!/usr/bin/env php
<?php

/**
 * Legacy PHPUnit migration helper.
 *
 * Intended for the OpenSRS PHP Toolkit test suite when running on
 * PHP 7.4 through PHP 8.4 with modern PHPUnit versions.
 *
 * Run from the project root:
 *
 *     php tools/migrate-legacy-phpunit.php
 *
 * This script performs only relatively safe mechanical conversions.
 * APIs that may require semantic changes are reported for manual review.
 */

$root = getcwd();
$testsDir = $root . DIRECTORY_SEPARATOR . 'tests';

if (!is_dir($testsDir)) {
    fwrite(
        STDERR,
        "Could not find tests/ directory.\n" .
        "Run this script from the project root.\n"
    );

    exit(1);
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(
        $testsDir,
        FilesystemIterator::SKIP_DOTS
    )
);

$changed = array();
$manualReview = array();

/**
 * Legacy PHPUnit APIs that should not be blindly replaced.
 */
$legacyPatterns = array(
    'setExpectedException',
    'assertInternalType',
    'assertAttribute',
    'assertAttributeEquals',
    'assertAttributeSame',
    'assertContainsOnly',
    'PHPUnit_Framework_',
    '->at(',
);

/**
 * Return a path relative to the repository root.
 */
function relativePath($path, $root)
{
    return substr($path, strlen($root) + 1);
}

foreach ($iterator as $file) {
    if (!$file->isFile()) {
        continue;
    }

    if (strtolower($file->getExtension()) !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    $content = file_get_contents($path);

    if ($content === false) {
        fwrite(STDERR, "Unable to read: " . $path . PHP_EOL);
        continue;
    }

    $original = $content;

    /*
     * PHPUnit 4:
     *
     *     class ExampleTest extends PHPUnit_Framework_TestCase
     *
     * Modern PHPUnit:
     *
     *     class ExampleTest extends \PHPUnit\Framework\TestCase
     */
    $content = str_replace(
        'PHPUnit_Framework_TestCase',
        '\\PHPUnit\\Framework\\TestCase',
        $content
    );

    /*
     * Modernize setUp() fixture signatures.
     *
     * Old:
     *
     *     public function setUp()
     *
     * Modern:
     *
     *     protected function setUp(): void
     */
    $content = preg_replace(
        '/(?:public|protected)\s+function\s+setUp\s*\(\s*\)\s*(?!\s*:)/',
        'protected function setUp(): void',
        $content
    );

    /*
     * Modernize tearDown() fixture signatures.
     */
    $content = preg_replace(
        '/(?:public|protected)\s+function\s+tearDown\s*\(\s*\)\s*(?!\s*:)/',
        'protected function tearDown(): void',
        $content
    );

    /*
     * Modernize setUpBeforeClass().
     */
    $content = preg_replace(
        '/(?:public|protected)\s+static\s+function\s+setUpBeforeClass\s*\(\s*\)\s*(?!\s*:)/',
        'public static function setUpBeforeClass(): void',
        $content
    );

    /*
     * Modernize tearDownAfterClass().
     */
    $content = preg_replace(
        '/(?:public|protected)\s+static\s+function\s+tearDownAfterClass\s*\(\s*\)\s*(?!\s*:)/',
        'public static function tearDownAfterClass(): void',
        $content
    );

    if ($content !== $original) {
        if (file_put_contents($path, $content) === false) {
            fwrite(STDERR, "Unable to write: " . $path . PHP_EOL);
            continue;
        }

        $changed[] = relativePath($path, $root);
    }

    /*
     * Search for APIs that still require manual migration.
     *
     * strpos() is intentionally used instead of str_contains()
     * because this helper itself must run on PHP 7.4.
     */
    foreach ($legacyPatterns as $pattern) {
        if (strpos($content, $pattern) !== false) {
            if (!isset($manualReview[$pattern])) {
                $manualReview[$pattern] = array();
            }

            $manualReview[$pattern][] = relativePath($path, $root);
        }
    }
}

echo PHP_EOL;
echo "Legacy PHPUnit migration pass complete." . PHP_EOL;
echo str_repeat('=', 40) . PHP_EOL;
echo PHP_EOL;

if (!empty($changed)) {
    echo "Changed files:" . PHP_EOL;

    foreach ($changed as $file) {
        echo "  - " . $file . PHP_EOL;
    }
} else {
    echo "No mechanical changes were required." . PHP_EOL;
}

echo PHP_EOL;
echo "Manual review:" . PHP_EOL;

if (empty($manualReview)) {
    echo "  No known legacy PHPUnit API patterns remain." . PHP_EOL;
} else {
    foreach ($manualReview as $pattern => $files) {
        echo PHP_EOL;
        echo "  Pattern: " . $pattern . PHP_EOL;

        foreach (array_unique($files) as $file) {
            echo "    - " . $file . PHP_EOL;
        }
    }
}

echo PHP_EOL;
echo str_repeat('=', 40) . PHP_EOL;
echo "Suggested next steps:" . PHP_EOL;
echo PHP_EOL;
echo "  vendor/bin/phpunit --version" . PHP_EOL;
echo "  vendor/bin/phpunit" . PHP_EOL;
echo PHP_EOL;

exit(0);
