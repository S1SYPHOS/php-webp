<?php

namespace WebPConvert\Converters;

use WebPConvert\ConverterAbstract;

/**
 * Class Imagick
 *
 * Converts an image to WebP via ImageMagick
 *
 * @package WebPConvert\Converters
 */
class Imagick extends ConverterAbstract
{
    public function checkRequirements()
    {
        if (!extension_loaded('imagick')) {
            throw new \Exception('Required iMagick extension is not available.');
        }

        if (!class_exists('Imagick')) {
            throw new \Exception('iMagick is installed but cannot handle source file.');
        }

        return true;
    }

    public function convertImage()
    {
        try {
            $this->checkRequirements();

            $im = new \Imagick($this->source);

            // Throws an exception if iMagick does not support WebP conversion
            if (!in_array('WEBP', $im->queryFormats())) {
                throw new \Exception('iMagick was compiled without WebP support.');
            }

            $im->setImageFormat('WEBP');
        } catch (\Exception $e) {
            return false; // TODO: `throw` custom \Exception $e & handle it smoothly on top-level.
        }

        // Apply losless compression for PNG images
        switch ($this->extension) {
            case 'png':
                $im->setOption('webp:lossless', 'true');
                break;
            default:
                break;
        }

        /*
         * More about iMagick's WebP options:
         * http://www.imagemagick.org/script/webp.php
         * https://stackoverflow.com/questions/37711492/imagemagick-specific-webp-calls-in-php
         */

        if (defined('WEBPCONVERT_IMAGICK_METHOD')) {
            $im->setOption('webp:method', WEBPCONVERT_IMAGICK_METHOD);
        } else {
            $im->setOption('webp:method', '6');
        }

        if (!defined('WEBPCONVERT_IMAGICK_LOW_MEMORY')) {
            $im->setOption('webp:low-memory', 'true');
        } else {
            $im->setOption('webp:low-memory', (
                WEBPCONVERT_IMAGICK_LOW_MEMORY
                ? 'true'
                : 'false'
            ));
        }

        $im->setImageCompressionQuality($this->quality);

        // TODO: Check out other iMagick methods, see http://php.net/manual/de/imagick.writeimage.php#114714
        // 1. file_put_contents($destination, $im)
        // 2. $im->writeImage($destination)
        $success = $im->writeImageFile(fopen($this->destination, 'wb'));

        // TODO: Check whether Imagick::writeImageFile returns false if conversion was unsuccessful
        if (!$success) {
            return false;
        }

        return true;
    }
}
