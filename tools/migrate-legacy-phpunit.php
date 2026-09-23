#!/usr/bin/env php
<?php

/**
 * Legacy PHPUnit migration helper for the OpenSRS PHP Toolkit.
 *
 * The repository intentionally keeps the original test sources close to
 * upstream. GitHub Actions runs this helper on its temporary checkout before
 * executing the tests.
 *
 * Compatible with PHP 7.4 through PHP 8.4.
 */

$root = getcwd();
$testsDir = $root . DIRECTORY_SEPARATOR . 'tests';

if (!is_dir($testsDir)) {
    fwrite(STDERR, "Could not find tests/ directory." . PHP_EOL);
    fwrite(STDERR, "Run this script from the project root." . PHP_EOL);
    exit(1);
}

function relativePath($path, $root)
{
    return substr($path, strlen($root) + 1);
}

/**
 * Find the end of a PHP method-call argument list.
 *
 * Handles quoted strings, escapes and nested (), [] and {}.
 */
function findClosingParenthesis($content, $openPos)
{
    $length = strlen($content);
    $depth = 0;
    $quote = null;
    $escaped = false;

    for ($i = $openPos; $i < $length; $i++) {
        $ch = $content[$i];

        if ($quote !== null) {
            if ($escaped) {
                $escaped = false;
                continue;
            }

            if ($ch === '\\') {
                $escaped = true;
                continue;
            }

            if ($ch === $quote) {
                $quote = null;
            }

            continue;
        }

        if ($ch === "'" || $ch === '"') {
            $quote = $ch;
            continue;
        }

        if ($ch === '(') {
            $depth++;
            continue;
        }

        if ($ch === ')') {
            $depth--;

            if ($depth === 0) {
                return $i;
            }
        }
    }

    return false;
}

/**
 * Split a PHP argument list on top-level commas.
 */
function splitArguments($arguments)
{
    $result = array();
    $start = 0;
    $length = strlen($arguments);
    $paren = 0;
    $bracket = 0;
    $brace = 0;
    $quote = null;
    $escaped = false;

    for ($i = 0; $i < $length; $i++) {
        $ch = $arguments[$i];

        if ($quote !== null) {
            if ($escaped) {
                $escaped = false;
                continue;
            }

            if ($ch === '\\') {
                $escaped = true;
                continue;
            }

            if ($ch === $quote) {
                $quote = null;
            }

            continue;
        }

        if ($ch === "'" || $ch === '"') {
            $quote = $ch;
            continue;
        }

        if ($ch === '(') {
            $paren++;
        } elseif ($ch === ')') {
            $paren--;
        } elseif ($ch === '[') {
            $bracket++;
        } elseif ($ch === ']') {
            $bracket--;
        } elseif ($ch === '{') {
            $brace++;
        } elseif ($ch === '}') {
            $brace--;
        } elseif (
            $ch === ',' &&
            $paren === 0 &&
            $bracket === 0 &&
            $brace === 0
        ) {
            $result[] = trim(substr($arguments, $start, $i - $start));
            $start = $i + 1;
        }
    }

    $tail = trim(substr($arguments, $start));

    if ($tail !== '' || !empty($result)) {
        $result[] = $tail;
    }

    return $result;
}

function lineIndentAt($content, $position)
{
    $prefix = substr($content, 0, $position);
    $lastNewline = strrpos($prefix, "\n");

    if ($lastNewline === false) {
        $line = $prefix;
    } else {
        $line = substr($prefix, $lastNewline + 1);
    }

    if (preg_match('/^[ \t]*/', $line, $matches)) {
        return $matches[0];
    }

    return '';
}

function isEmptyStringLiteral($value)
{
    $value = trim($value);

    return $value === "''" || $value === '""';
}

function isNullLiteral($value)
{
    return strtolower(trim($value)) === 'null';
}

/**
 * Rewrite one family of legacy PHPUnit method calls.
 */
function rewriteLegacyExceptionMethod($content, $method)
{
    $needle = '$this->' . $method;
    $offset = 0;

    while (($start = strpos($content, $needle, $offset)) !== false) {
        $openPos = strpos($content, '(', $start + strlen($needle));

        if ($openPos === false) {
            break;
        }

        $closePos = findClosingParenthesis($content, $openPos);

        if ($closePos === false) {
            $offset = $start + strlen($needle);
            continue;
        }

        $endPos = $closePos + 1;

        while (
            $endPos < strlen($content) &&
            ($content[$endPos] === ' ' || $content[$endPos] === "\t")
        ) {
            $endPos++;
        }

        if ($endPos < strlen($content) && $content[$endPos] === ';') {
            $endPos++;
        }

        $rawArgs = substr(
            $content,
            $openPos + 1,
            $closePos - $openPos - 1
        );

        $args = splitArguments($rawArgs);

        if (empty($args) || trim($args[0]) === '') {
            $offset = $endPos;
            continue;
        }

        $indent = lineIndentAt($content, $start);
        $lines = array();

        $lines[] = '$this->expectException(' . $args[0] . ');';

        if ($method === 'setExpectedExceptionRegExp') {
            if (isset($args[1]) && trim($args[1]) !== '') {
                $lines[] =
                    '$this->expectExceptionMessageMatches(' .
                    $args[1] .
                    ');';
            }

            if (
                isset($args[2]) &&
                trim($args[2]) !== '' &&
                !isNullLiteral($args[2])
            ) {
                $lines[] =
                    '$this->expectExceptionCode(' .
                    $args[2] .
                    ');';
            }
        } else {
            if (
                isset($args[1]) &&
                trim($args[1]) !== '' &&
                !isEmptyStringLiteral($args[1])
            ) {
                $lines[] =
                    '$this->expectExceptionMessage(' .
                    $args[1] .
                    ');';
            }

            if (
                isset($args[2]) &&
                trim($args[2]) !== '' &&
                !isNullLiteral($args[2])
            ) {
                $lines[] =
                    '$this->expectExceptionCode(' .
                    $args[2] .
                    ');';
            }
        }

        $replacement = array_shift($lines);

        foreach ($lines as $line) {
            $replacement .= PHP_EOL . $indent . $line;
        }

        $content =
            substr($content, 0, $start) .
            $replacement .
            substr($content, $endPos);

        $offset = $start + strlen($replacement);
    }

    return $content;
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(
        $testsDir,
        FilesystemIterator::SKIP_DOTS
    )
);

$changed = array();
$manualReview = array();

$legacyPatterns = array(
    'assertInternalType',
    'assertNotInternalType',
    'assertAttribute',
    'assertAttributeEquals',
    'assertAttributeSame',
    'assertContainsOnly',
    'PHPUnit_Framework_',
    '->at(',
);

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
     * PHPUnit 4 base class.
     *
     * Replace the variant with the existing leading slash first to avoid
     * generating "\\PHPUnit\Framework\TestCase".
     */
    $content = str_replace(
        array(
            '\\PHPUnit_Framework_TestCase',
            'PHPUnit_Framework_TestCase',
        ),
        array(
            '\\PHPUnit\\Framework\\TestCase',
            '\\PHPUnit\\Framework\\TestCase',
        ),
        $content
    );

    /*
     * Fixture method signatures required by modern PHPUnit.
     */
    $content = preg_replace(
        '/(?:public|protected)\s+function\s+setUp\s*\(\s*\)\s*(?!\s*:)/',
        'protected function setUp(): void',
        $content
    );

    $content = preg_replace(
        '/(?:public|protected)\s+function\s+tearDown\s*\(\s*\)\s*(?!\s*:)/',
        'protected function tearDown(): void',
        $content
    );

    $content = preg_replace(
        '/(?:public|protected)\s+static\s+function\s+setUpBeforeClass\s*\(\s*\)\s*(?!\s*:)/',
        'public static function setUpBeforeClass(): void',
        $content
    );

    $content = preg_replace(
        '/(?:public|protected)\s+static\s+function\s+tearDownAfterClass\s*\(\s*\)\s*(?!\s*:)/',
        'public static function tearDownAfterClass(): void',
        $content
    );

    /*
     * PHPUnit 4 exception expectation APIs.
     *
     * setExpectedExceptionRegExp($class, $regex[, $code])
     *     ->
     * expectException($class)
     * expectExceptionMessageMatches($regex)
     * expectExceptionCode($code)
     *
     * setExpectedException($class[, $message[, $code]])
     *     ->
     * expectException($class)
     * expectExceptionMessage($message)
     * expectExceptionCode($code)
     */
    $content = rewriteLegacyExceptionMethod(
        $content,
        'setExpectedExceptionRegExp'
    );

    $content = rewriteLegacyExceptionMethod(
        $content,
        'setExpectedException'
    );

    /*
     * Regular-expression assertion names changed in modern PHPUnit.
     */
    $content = str_replace(
        array(
            '->assertRegExp(',
            '->assertNotRegExp(',
        ),
        array(
            '->assertMatchesRegularExpression(',
            '->assertDoesNotMatchRegularExpression(',
        ),
        $content
    );

    if ($content !== $original) {
        if (file_put_contents($path, $content) === false) {
            fwrite(STDERR, "Unable to write: " . $path . PHP_EOL);
            continue;
        }

        $changed[] = relativePath($path, $root);
    }

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
echo str_repeat('=', 46) . PHP_EOL;

if (!empty($changed)) {
    echo PHP_EOL . "Changed files: " . count($changed) . PHP_EOL;
} else {
    echo PHP_EOL . "No mechanical changes were required." . PHP_EOL;
}

echo PHP_EOL . "Remaining legacy API patterns:" . PHP_EOL;

if (empty($manualReview)) {
    echo "  None detected." . PHP_EOL;
} else {
    foreach ($manualReview as $pattern => $files) {
        echo "  " . $pattern . ": " . count(array_unique($files)) . " file(s)" . PHP_EOL;
    }
}

echo PHP_EOL;
exit(0);
