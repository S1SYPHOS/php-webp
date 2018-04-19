<?php

namespace PHPWebP;

/**
 * Class ConverterAbstract
 *
 * Abstract class for all WebP converters
 *
 * @package PHPWebP
 */
abstract class ConverterAbstract
{
    protected $source;
    protected $destination;
    protected $quality;
    protected $strip;
    protected $extension;

    public function __construct($source, $destination, $quality = 85, $stripMetadata = false)
    {
        $this->source = $source;
        $this->destination = $destination;
        $this->quality = $quality;
        $this->strip = $stripMetadata;

        $this->extension = $this->getExtension($source);
    }

    // Forces every converter to implement the following functions:
    // `checkRequirements()` - checks if converter's requirements are met
    // `convert()` - converting given image to WebP
    abstract public function checkRequirements();
    abstract public function convert();

    /**
     *  Common functionality
     */

    /**
     * Returns given file's extension
     *
     * @param string $filePath
     *
     * @return string
     */
    public function getExtension($filePath)
    {
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        return strtolower($fileExtension);
    }

    // Returns escaped version of string
    public function escapeFilename($string)
    {
        // Escaping whitespaces & quotes
        $string = preg_replace('/\s/', '\\ ', $string);
        $string = filter_var($string, FILTER_SANITIZE_MAGIC_QUOTES);

        // Stripping control characters
        // see https://stackoverflow.com/questions/12769462/filter-flag-strip-low-vs-filter-flag-strip-high
        $string = filter_var($string, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);

        return $string;
    }
}
