<?php

/**
 * WebPConvert - Convert JPEG & PNG to WebP with PHP
 *
 * @link https://github.com/rosell-dk/webp-convert
 * @license MIT
 */

namespace WebPConvert\Tests\Converters;

use WebPConvert\Converters\Imagick;
use PHPUnit\Framework\TestCase;

class ImagickTest extends TestCase
{
    private $source;
    private $destination;

    public function __construct()
    {
        $this->imagick = new Imagick(
            realpath(__DIR__ . '/../test.jpg'),
            realpath(__DIR__ . '/../test.webp')
        );
    }

    public function testCheckRequirements()
    {
        $this->assertNotNull($this->imagick->checkRequirements());
    }

    public function testConvert()
    {
        $this->assertTrue($this->imagick->convertImage());
    }
}
