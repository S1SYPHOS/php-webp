<?php

/**
 * PHP-WebP - The WebP conversion library for PHP
 *
 * @link https://github.com/rosell-dk/webp-convert
 * @license MIT
 */

namespace PHPWebP\Tests\Converters;

use PHPWebP\Converters\Gd;
use PHPUnit\Framework\TestCase;

class GdTest extends TestCase
{
    private $source;
    private $destination;

    public function __construct()
    {
        $this->gd = new Gd(
            realpath(__DIR__ . '/../test.jpg'),
            realpath(__DIR__ . '/../test.webp')
        );
    }

    // public function testCheckRequirements()
    // {
    //     $this->assertNotNull($this->gd->checkRequirements());
    // }

    // public function testConvert()
    // {
    //     $this->assertTrue($this->gd->convert());
    // }
}
