<?php

/**
 * PHP-WebP - The WebP conversion library for PHP
 *
 * @link https://github.com/rosell-dk/webp-convert
 * @license MIT
 */

namespace PHPWebP\Tests\Converters;

use PHPWebP\Converters\Cwebp;
use PHPUnit\Framework\TestCase;

class CwebpTest extends TestCase
{
    private $source;
    private $destination;

    public function __construct()
    {
        $this->cwebp = new Cwebp(
            realpath(__DIR__ . '/../test.jpg'),
            realpath(__DIR__ . '/../test.webp')
        );
    }

    public function testCheckRequirements()
    {
        $this->assertNotNull($this->cwebp->checkRequirements());
    }

    public function testSetUpBinaries()
    {
        $this->assertNotEmpty($this->cwebp->setUpBinaries());
    }

    public function testHasNiceSupport()
    {
        $this->assertNotNull($this->cwebp->hasNiceSupport());
    }

    public function testConvert()
    {
        $this->assertTrue($this->cwebp->convert());
    }
}
