<?php

/**
 * WebPConvert - Convert JPEG & PNG to WebP with PHP
 *
 * @link https://github.com/rosell-dk/webp-convert
 * @license MIT
 */

namespace WebPConvert\Tests;

use WebPConvert\WebPConvert;
use PHPUnit\Framework\TestCase;

class WebPConvertTest extends TestCase
{
    /**
     * @expectedException \Exception
     */
    public function testIsValidTargetInvalid()
    {
        $wpc = new WebPConvert();
        $wpc->isValidTarget('Invalid');
    }

    public function testIsValidTargetValid()
    {
        $wpc = new WebPConvert();
        $this->assertTrue($wpc->isValidTarget(__FILE__));
    }

    /**
     * @expectedException \Exception
     */
    public function testIsAllowedExtensionInvalid()
    {
        $wpc = new WebPConvert();
        $allowed = ['jpg', 'jpeg', 'png'];

        foreach ($allowed as $key) {
            $wpc->isAllowedExtension(__FILE__);
        }
    }

    public function testIsAllowedExtensionValid()
    {
        $wpc = new WebPConvert();
        $source = (__DIR__ . '/test.jpg');
        $this->assertTrue($wpc->isAllowedExtension($source));
    }

    public function testCreateWritableFolder()
    {
        $wpc = new WebPConvert();
        $source = (__DIR__ . '/test/test.file');
        $path = pathinfo($source, PATHINFO_DIRNAME);
        $wpc->createWritableFolder($source);
        $this->assertDirectoryExists($path);
        $this->assertDirectoryIsWritable($path);
    }

    public function testDefaultConverterOrder()
    {
        $wpc = new WebPConvert();
        $default = ['imagick', 'cwebp', 'gd', 'ewww', 'optimus']; // optimized converter order ('default order')
        $this->assertEquals($default, $wpc->setUpConverters());
    }

    public function testSetGetConverters()
    {
        $wpc = new WebPConvert();
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
        $wpc = new WebPConvert();
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
        $wpc = new WebPConvert();
        $source = (__DIR__ . '/test.jpg');
        $destination = (__DIR__ . '/test.webp');
        $wpc->setConverters(['imagick', 'cwebp', 'gd', 'ewww']);

        $this->assertTrue($wpc->convert($source, $destination));
    }
}
