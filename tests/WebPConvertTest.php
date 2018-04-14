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
        WebPConvert::isValidTarget('Invalid');
    }

    public function testIsValidTargetValid()
    {
        $this->assertTrue(WebPConvert::isValidTarget(__FILE__));
    }

    /**
     * @expectedException \Exception
     */
    public function testIsAllowedExtensionInvalid()
    {
        $allowed = ['jpg', 'jpeg', 'png'];

        foreach ($allowed as $key) {
            WebPConvert::isAllowedExtension(__FILE__);
        }
    }

    public function testIsAllowedExtensionValid()
    {
        $source = (__DIR__ . '/test.jpg');

        $this->assertTrue(WebPConvert::isAllowedExtension($source));
    }

    public function testCreateWritableFolder()
    {
        $source = (__DIR__ . '/test/test.file');
        $path = pathinfo($source, PATHINFO_DIRNAME);

        $this->assertTrue(WebPConvert::createWritableFolder($source));
        $this->assertDirectoryExists($path);
        $this->assertDirectoryIsWritable($path);
    }

    public function testDefaultConverterOrder()
    {
        // Tests optimized converter order ('default order')
        $default = ['imagick', 'cwebp', 'gd', 'ewww'];

        $this->assertEquals($default, WebPConvert::prepareConverters());
    }

    public function testSetGetConverters()
    {
        // Tests resetting converters
        WebPConvert::setConverters();

        $this->assertEmpty(WebPConvert::getConverters());

        // Tests correct setting of converters ('natural order')
        $natural = ['cwebp', 'ewww', 'gd', 'imagick'];
        WebPConvert::setConverters($natural);

        $this->assertEquals($natural, WebPConvert::getConverters());

        // Tests excluding default binaries ('exclusive order')
        $exclusive = ['gd', 'cwebp'];
        WebPConvert::setConverters($exclusive, true);

        $this->assertEquals($exclusive, WebPConvert::getConverters());
    }

    public function testPrepareConverters()
    {
        WebPConvert::setConverters();
        $natural = ['cwebp', 'ewww', 'gd', 'imagick'];

        $this->assertEquals($natural, WebPConvert::prepareConverters());

        // Tests excluding default binaries
        $preferred = ['gd', 'cwebp'];
        WebPConvert::setConverters($preferred, true);

        $this->assertEquals($preferred, WebPConvert::prepareConverters());
    }

    public function testConvert()
    {
        $source = (__DIR__ . '/test.jpg');
        $destination = (__DIR__ . '/test.webp');
        WebPConvert::setConverters(['imagick', 'cwebp', 'gd', 'ewww']);

        $this->assertTrue(WebPConvert::convert($source, $destination));
    }
}
