<?php

namespace WebPConvert;

/**
 * Class WebPConvert
 *
 * Converts JPEG & PNG to WebP with PHP
 *
 * @package WebPConvert
 */
class WebPConvert
{
    public static $preferredConverters = [];
    public static $excludeDefaultBinaries = false;

    // Defines the array of preferred converters
    public static function setConverters($array, $exclude = false)
    {
        self::$preferredConverters = $array;

        if ($exclude) {
            self::$excludeDefaultBinaries = true;
        }
    }

    public static function getConverters()
    {
        // Prepare building up an array of converters
        $converters = [];

        // Saves all available converters inside the `Converters` directory to an array
        $availableConverters = array_map(function ($filePath) {
            $fileName = basename($filePath, '.php');
            return strtolower($fileName);
        }, glob(__DIR__ . '/Converters/*.php'));

        // Checks if preferred converters match available converters and adds all matches to $converters array
        foreach (self::$preferredConverters as $preferredConverter) {
            if (in_array($preferredConverter, $availableConverters)) {
                $converters[] = $preferredConverter;
            }
        }

        if (self::$excludeDefaultBinaries) {
            return $converters;
        }

        // Fills $converters array with the remaining available converters, keeping the updated order of execution
        foreach ($availableConverters as $availableConverter) {
            if (in_array($availableConverter, $converters)) {
                continue;
            }
            $converters[] = $availableConverter;
        }

        return $converters;
    }

    /**
     * TODO: DocBlock
     */
    public static function convert($source, $destination, $quality = 85, $stripMetadata = true)
    {
        try {
            foreach (self::getConverters() as $converter) {
                $converter = ucfirst($converter);
                $className = 'WebPConvert\\Converters\\' . $converter;

                if (class_exists($className)) {
                    $object = new $className(
                        $source,
                        $destination,
                        $quality,
                        $stripMetadata
                    );
                }

                if (!is_callable([$object, 'convert'])) {
                    continue;
                }

                $conversion = call_user_func([$object, 'convert']);

                if ($conversion) {
                    $success = true;
                    break;
                }

                $success = false;
            }

            return $success;
        } catch (\Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
}
