<?php

/**
 * WebPConvert - Convert JPEG & PNG to WebP with PHP
 *
 * @link https://github.com/rosell-dk/webp-convert
 * @license MIT
 */

namespace WebPConvert\Tests\Converters;

use WebPConvert\Converters\Cwebp;
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

    public function testPrepareBinaries()
    {
        $this->assertNotEmpty($this->cwebp->prepareBinaries());
    }

    public function testHasNiceSupport()
    {
        $this->assertNotNull($this->cwebp->hasNiceSupport());
    }

    public function testConvert()
    {
        $this->assertTrue($this->cwebp->convertImage());
    }
}
