<?php

namespace WebPConvert\Converters;

use WebPConvert\ConverterAbstract;

/**
 * Class Gd
 *
 * Converts an image to WebP via GD Graphics (Draw)
 *
 * @package WebPConvert\Converters
 */
class Gd extends ConverterAbstract
{
    public function checkRequirements()
    {
        if (!extension_loaded('gd')) {
            throw new \Exception('Required GD extension is not available.');
        }

        if (!function_exists('imagewebp')) {
            throw new \Exception('Required imagewebp() function is not available.');
        }

        return true;
    }

    public function convert()
    {
        try {
            $this->checkRequirements();

            switch ($this->extension) {
                case 'png':
                    if (defined('WEBPCONVERT_GD_PNG') && WEBPCONVERT_GD_PNG) {
                        return imagecreatefrompng($this->source);
                    } else {
                        // TODO: Troubleshooting section: define("WEBPCONVERT_GD_PNG", true);
                        throw new \Exception('PNG file conversion failed.');
                    }
                    break;
                default:
                    $image = imagecreatefromjpeg($this->source);
            }

            // Checks if either imagecreatefromjpeg() or imagecreatefrompng() returned false
            if (!$image) {
                throw new \Exception('Either imagecreatefromjpeg or imagecreatefrompng failed');
            }
        } catch (\Exception $e) {
            return false; // TODO: `throw` custom \Exception $e & handle it smoothly on top-level.
        }

        $success = imagewebp($image, $this->destination, $this->quality);

        /*
         * This hack solves an `imagewebp` bug
         * See https://stackoverflow.com/questions/30078090/imagewebp-php-creates-corrupted-webp-files
         *
         */

        if (filesize($this->destination) % 2 == 1) {
            file_put_contents($this->destination, "\0", FILE_APPEND);
        }

        imagedestroy($image);

        return $success;
    }
}
