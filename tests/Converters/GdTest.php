<?php

/**
 * WebPConvert - Convert JPEG & PNG to WebP with PHP
 *
 * @link https://github.com/rosell-dk/webp-convert
 * @license MIT
 */

namespace WebPConvert\Tests\Converters;

use WebPConvert\Converters\Gd;
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

    public function testCheckRequirements()
    {
        $this->assertNotNull($this->gd->checkRequirements());
    }

    public function testConvert()
    {
        $this->assertTrue($this->gd->convert());
    }
}
