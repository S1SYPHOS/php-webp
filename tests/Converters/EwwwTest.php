<?php

/**
 * WebPConvert - Convert JPEG & PNG to WebP with PHP
 *
 * @link https://github.com/rosell-dk/webp-convert
 * @license MIT
 */

namespace WebPConvert\Tests\Converters;

use WebPConvert\Converters\Ewww;
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
        $configPath = realpath(__DIR__ . '/../../config.json');
        $configFile = file_get_contents($configPath);
        $configArray = json_decode($configFile, true);

        define("WEBPCONVERT_EWWW_KEY", $configArray['ewww']);

        $this->assertNotNull($this->ewww->checkRequirements());
    }

    public function testConvert()
    {
        $this->assertTrue($this->ewww->convertImage());
    }
}
