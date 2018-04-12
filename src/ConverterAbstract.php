<?php

namespace WebPConvert;

/**
 * Class ConverterAbstract
 *
 * Abstract class for all WebP converters
 *
 * @package WebPConvert
 */
abstract class ConverterAbstract
{
    // protected $extension = '';
    protected $allowedExtensions = ['jpg', 'jpeg', 'png'];

    public function __construct($source, $destination, $quality, $stripMetadata)
    {
        try {
            $this->isValidTarget($source);
            $this->isAllowedExtension($source);
            $this->createWritableFolder($destination);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // Forces every converter to implement `convert()` function
    abstract protected function convert();

    /**
     *  Common functionality
     */

    // Returns given file's extension
    public function getExtension($filePath)
    {
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        return strtolower($fileExtension);
    }

    // Throws an exception if the provided file doesn't exist
    public function isValidTarget($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception('File or directory not found: ' . $filePath);
        }

        return true;
    }

    // TODO: Write extension of source file into variable
    // Throws an exception if the provided file's extension is invalid
    public function isAllowedExtension($filePath)
    {
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!in_array(strtolower($fileExtension), self::$allowedExtensions)) {
            throw new \Exception('Unsupported file extension: ' . $fileExtension);
        }

        return true;
    }

    // Creates folder in provided path & sets correct permissions
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

        return true;
    }
}