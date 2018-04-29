<?php

/**
 * PHP-WebP - The WebP conversion library for PHP
 *
 * @link https://github.com/rosell-dk/webp-convert
 * @license MIT
 */

namespace PHPWebP\Tests\Converters;

use PHPWebP\Converters\Ewww;
use PHPUnit\Framework\TestCase;

class EwwwTest extends TestCase
{
    private $source;
    private $destination;

    public function __construct()
    {
        $this->ewww = new Ewww(
            realpath(__DIR__ . '/../test.jpg'),
            realpath(__DIR__ . '/../test.webp')
        );
    }

    public function testCheckRequirements()
    {
        define("PHPWEBP_EWWW_KEY", "key-abc123");

        $this->assertNotNull($this->ewww->checkRequirements());
    }

    public function testIsValidKeyInvalid()
    {
        $this->assertFalse($this->ewww->isValidKey());
        $this->assertFalse($this->ewww->isValidKey('key-abc123'));
    }

    // public function testConvert()
    // {
    //     $this->assertNotNull($this->ewww->convert());
    // }
}
