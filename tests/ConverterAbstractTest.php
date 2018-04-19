<?php

/**
 * PHP-WebP - The WebP conversion library for PHP
 *
 * @link https://github.com/rosell-dk/webp-convert
 * @license MIT
 */

namespace PHPWebP\Tests;

use PHPWebP\ConverterAbstract;
use PHPUnit\Framework\TestCase;

class ConverterAbstractTest extends TestCase
{
    private $source;
    private $destination;
    private $mockObject;

    public function __construct()
    {
        $this->source = realpath(__DIR__ . '/../test.jpg');
        $this->destination = realpath(__DIR__ . '/../test.webp');
        $this->mockObject = $this
            ->getMockBuilder('PHPWebP\ConverterAbstract')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass()
        ;
    }

    public function testGetExtension()
    {
        $this->assertEquals('php', $this->mockObject->getExtension(__FILE__));
    }

    public function testEscapeFilename()
    {
        $wrong = '/path/to/file Na<>me."ext"';
        $right = '/path/to/file\\\ Name.\&#34;ext\&#34;';

        $this->assertEquals($right, $this->mockObject->escapeFilename($wrong));
    }
}
