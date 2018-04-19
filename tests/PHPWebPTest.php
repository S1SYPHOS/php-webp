<?php

/**
 * PHP-WebP - The WebP conversion library for PHP
 *
 * @link https://github.com/rosell-dk/webp-convert
 * @license MIT
 */

namespace PHPWebP\Tests;

use PHPWebP\PHPWebP;
use PHPUnit\Framework\TestCase;

class PHPWebPTest extends TestCase
{
    /**
     * @expectedException \Exception
     */
    public function testIsValidTargetInvalid()
    {
        $wpc = new PHPWebP();
        $wpc->isValidTarget('Invalid');
    }

    public function testIsValidTargetValid()
    {
        $wpc = new PHPWebP();
        $this->assertTrue($wpc->isValidTarget(__FILE__));
    }

    /**
     * @expectedException \Exception
     */
    public function testIsAllowedExtensionInvalid()
    {
        $wpc = new PHPWebP();
        $allowed = ['jpg', 'jpeg', 'png'];

        foreach ($allowed as $key) {
            $wpc->isAllowedExtension(__FILE__);
        }
    }

    public function testIsAllowedExtensionValid()
    {
        $wpc = new PHPWebP();
        $source = (__DIR__ . '/test.jpg');
        $this->assertTrue($wpc->isAllowedExtension($source));
    }

    public function testCreateWritableFolder()
    {
        $wpc = new PHPWebP();
        $source = (__DIR__ . '/test/test.file');
        $path = pathinfo($source, PATHINFO_DIRNAME);
        $wpc->createWritableFolder($source);
        $this->assertDirectoryExists($path);
        $this->assertDirectoryIsWritable($path);
    }

    public function testDefaultConverterOrder()
    {
        $wpc = new PHPWebP();
        $default = ['imagick', 'cwebp', 'gd', 'ewww', 'optimus']; // optimized converter order ('default order')
        $this->assertEquals($default, $wpc->setUpConverters());
    }

    public function testSetGetConverters()
    {
        $wpc = new PHPWebP();
        $this->assertEmpty($wpc->getConverters());

        // Tests correct setting of converters ('natural order')
        $natural = ['cwebp', 'ewww', 'gd', 'imagick'];
        $wpc->setConverters($natural);
        $this->assertEquals($natural, $wpc->getConverters());

        // Tests excluding default binaries ('exclusive order')
        $exclusive = ['gd', 'cwebp'];
        $wpc->setConverters($exclusive);
        $wpc->skipDefaultConverters();
        $this->assertEquals($exclusive, $wpc->getConverters());
    }

    public function testsetUpConverters()
    {
        $wpc = new PHPWebP();
        $natural = ['imagick', 'cwebp', 'gd', 'ewww', 'optimus'];
        $this->assertEquals($natural, $wpc->setUpConverters());

        // Tests excluding default binaries
        $preferred = ['gd', 'cwebp'];
        $wpc->setConverters($preferred);
        $wpc->skipDefaultConverters();

        $this->assertEquals($preferred, $wpc->setUpConverters());
    }

    public function testConvert()
    {
        $wpc = new PHPWebP();
        $source = (__DIR__ . '/test.jpg');
        $destination = (__DIR__ . '/test.webp');
        $wpc->setConverters(['imagick', 'cwebp', 'gd', 'ewww']);

        $this->assertTrue($wpc->convert($source, $destination));
    }
}
