<?php

/**
 * PHPWebP - The WebP conversion library for PHP
 *
 * @link https://github.com/S1SYPHOS/webp-convert
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace PHPWebP;

/**
 * Class PHPWebP
 *
 * Converts JPEG & PNG to WebP with PHP
 *
 * @package PHPWebP
 */
class PHPWebP
{
    /**
     * Current version number of PHPWebP
     */
    const VERSION = '1.0.0';

    /**
     * Available converters (in order of capability)
     *
     * @var array
     */
    private $converters = ['imagick', 'cwebp', 'gd', 'ewww', 'optimus'];

    /**
     * Preferred converters
     *
     * @var array
     */
    protected $preferredConverters = [];

    /**
     * Whether to skip available converters when preferred ones are set
     *
     * @var boolean
     */
    protected $skipDefaultConverters = false;

    /**
     * File extensions viable for WebP conversion
     *
     * @var array
     */
    protected $allowedExtensions = ['jpg', 'jpeg', 'png'];

    public function __construct($converters = null)
    {
        if ($converters != null) {
            $this->setConverters($converters);
        }
    }

    /**
     * Checks whether provided file exists
     *
     * @param string $filePath
     *
     * @return boolean
     */
    public function isValidTarget($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception('File or directory not found: ' . $filePath);
        }

        return true;
    }

    /**
     * Checks whether provided file's extension is viable for WebP conversion
     *
     * @param string $filePath
     *
     * @return boolean
     */
    public function isAllowedExtension($filePath)
    {
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!in_array(strtolower($fileExtension), $this->allowedExtensions)) {
            throw new \Exception('Unsupported file extension: ' . $fileExtension);
        }

        return true;
    }

    /**
     * Creates folder in provided path & sets correct permissions
     *
     * @param string $filePath
     */
    public function createWritableFolder($filePath)
    {
        $folder = pathinfo($filePath, PATHINFO_DIRNAME);

        if (!file_exists($folder)) {
            // TODO: what if this is outside open basedir?
            // see http://php.net/manual/en/ini.core.php#ini.open-basedir

            // First, we have to figure out which permissions to set.
            // We want same permissions as parent folder
            // But which parent? - the parent to the first missing folder

            $parentFolders = explode('/', $folder);
            $poppedFolders = [];

            while (!(file_exists(implode('/', $parentFolders)))) {
                array_unshift($poppedFolders, array_pop($parentFolders));
            }

            // Retrieving permissions of closest existing folder
            $closestExistingFolder = implode('/', $parentFolders);
            $permissions = fileperms($closestExistingFolder) & 000777;

            // Trying to create the given folder
            if (!mkdir($folder, $permissions, true)) {
                throw new \Exception('Failed creating folder: ' . $folder);
            }

            // `mkdir` doesn't respect permissions, so we have to `chmod` each created subfolder
            foreach ($poppedFolders as $subfolder) {
                $closestExistingFolder .= '/' . $subfolder;
                // Setting directory permissions
                chmod($folder, $permissions);
            }
        }

        // Checks if there's a file in $filePath & if writing permissions are correct
        if (file_exists($filePath) && !is_writable($filePath)) {
            throw new \Exception('Cannot overwrite ' . basename($filePath) . ' - check file permissions.');
        }

        // There's either a rewritable file in $filePath or none at all.
        // If there is, simply attempt to delete it
        if (file_exists($filePath) && !unlink($filePath)) {
            throw new \Exception('Existing file cannot be removed: ' . basename($filePath));
        }

        return;
    }

    /**
     * Sets preferred converter(s)
     *
     * Note:
     * Calling this function without arguments resets $preferredConverters, whereas
     * passing single converter as string also sets $skipDefaultConverters `true`
     *
     * @param array|string $preferred
     */
    public function setConverters($preferred = [])
    {
        if (is_string($preferred)) {
            $this->preferredConverters = (array) $preferred;
            $this->skipDefaultConverters();

            return;
        }
        $this->preferredConverters = $preferred;
    }

    /**
     * Gets preferred converter(s)
     *
     * @return array
     */
    public function getConverters()
    {
        return $this->preferredConverters;
    }

    /**
     * Sets whether to skip default converters (exclusively using preferred ones)
     *
     * @param boolean $skip
     */
    public function skipDefaultConverters($skip = true)
    {
        $this->skipDefaultConverters = $skip;
    }

    /**
     * Sets up converters to be used during conversion
     *
     * @return array
     */
    public function setUpConverters()
    {
        // Returns available converters if no preferred converters are set
        if (empty($this->preferredConverters)) {
            return $this->converters;
        }

        $converters = $this->preferredConverters;

        // Returns preferred converters if set & remaining ones be skipped
        if ($this->skipDefaultConverters) {
            return $converters;
        }

        // Saves converters inside `Converters` directory to an array
        $actualConverters = array_map(function ($filePath) {
            $fileName = basename($filePath, '.php');
            return strtolower($fileName);
        }, glob(__DIR__ . '/Converters/*.php'));

        // Fills $converters array with the remaining available converters, keeping the updated order of execution
        foreach ($this->converters as $converter) {
            if (in_array($converter, $converters)) {
                continue;
            }
            $converters[] = $converter;
        }

        return $converters + $actualConverters;
    }

    /**
     * Converts image to WebP
     *
     * @param string $source Path of input image
     * @param string $destination Path of output image
     * @param integer $quality Image compression quality (ranging from 0 to 100)
     * @param boolean $stripMetadata Whether to strip metadata
     *
     * @return boolean
     */
    public function convert($source, $destination, $quality = 85, $stripMetadata = true)
    {
        try {
            $this->isValidTarget($source);
            $this->isAllowedExtension($source);
            $this->createWritableFolder($destination);

            $success = false;

            // Sets up converters ..
            $currentConverters = $this->setUpConverters();

            // .. and iterates over them
            foreach ($currentConverters as $currentConverter) {
                $converterName = ucfirst(strtolower($currentConverter));
                $className = 'PHPWebP\\Converters\\' . $converterName;

                if (!class_exists($className)) {
                    continue;
                }

                $converter = new $className(
                    $source,
                    $destination,
                    $quality,
                    $stripMetadata
                );

                if (!$converter instanceof ConverterAbstract || !is_callable([$converter, 'convert'])) {
                    continue;
                }

                $conversion = call_user_func([$converter, 'convert']);

                if ($conversion) {
                    $success = true;
                    $this->setConverters($currentConverter);

                    break;
                }
            }

            return $success;
        } catch (\Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
}
