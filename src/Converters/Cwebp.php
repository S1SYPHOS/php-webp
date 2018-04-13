<?php

namespace WebPConvert\Converters;

use WebPConvert\ConverterAbstract;

/**
 * Class Cwebp
 *
 * Converts an image to WebP via `cwebp` binaries directly
 *
 * @package WebPConvert\Converters
 */
class Cwebp extends ConverterAbstract
{
    private $cwebpPaths;
    private $binaryInfo;

    public function __construct()
    {
        $this->cwebpPaths = [ // System paths to look for cwebp binary
            '/usr/bin/cwebp',
            '/usr/local/bin/cwebp',
            '/usr/gnu/bin/cwebp',
            '/usr/syno/bin/cwebp'
        ];

        $this->binaryInfo = [  // OS-specific binaries included in this library
            'WinNT' => [ 'cwebp.exe', '49e9cb98db30bfa27936933e6fd94d407e0386802cb192800d9fd824f6476873'],
            'Darwin' => [ 'cwebp-mac12', 'a06a3ee436e375c89dbc1b0b2e8bd7729a55139ae072ed3f7bd2e07de0ebb379'],
            'SunOS' => [ 'cwebp-sol', '1febaffbb18e52dc2c524cda9eefd00c6db95bc388732868999c0f48deb73b4f'],
            'FreeBSD' => [ 'cwebp-fbsd', 'e5cbea11c97fadffe221fdf57c093c19af2737e4bbd2cb3cd5e908de64286573'],
            'Linux' => [ 'cwebp-linux', '916623e5e9183237c851374d969aebdb96e0edc0692ab7937b95ea67dc3b2568']
        ][PHP_OS];
    }

    public function checkRequirements()
    {
        if (!function_exists('exec')) {
            throw new \Exception('exec() is not enabled.');
        }

        return true;
    }

    public function updateBinaries($file, $hash, $array)
    {
        $binaryFile = __DIR__ . '/Binaries/' . $file;

        // Throws an exception if binary file does not exist
        if (!file_exists($binaryFile)) {
            throw new \Exception('Operating system is currently not supported: ' . PHP_OS);
        }

        // File exists, now generate its hash
        $binaryHash = hash_file('sha256', $binaryFile);

        // Throws an exception if binary file checksum & deposited checksum do not match
        if ($binaryHash != $hash) {
            throw new \Exception('Binary checksum is invalid.');
        }

        array_unshift($array, $binaryFile);

        return $array;
    }

    // Checks if 'Nice' is available
    public function hasNiceSupport()
    {
        exec("nice 2>&1", $niceOutput);

        if (is_array($niceOutput) && isset($niceOutput[0])) {
            if (preg_match('/usage/', $niceOutput[0]) || (preg_match('/^\d+$/', $niceOutput[0]))) {
                /*
                 * Nice is available - default niceness (+10)
                 * https://www.lifewire.com/uses-of-commands-nice-renice-2201087
                 * https://www.computerhope.com/unix/unice.htm
                 */

                return true;
            }

            return false;
        }
    }

    public function convertImage()
    {
        try {
            $this->checkRequirements();

            // Checks if provided binary file & its hash match with deposited version & updates cwebp binary array
            $binaries = $this->updateBinaries(
                $this->binaryInfo[0],
                $this->binaryInfo[1],
                $this->cwebpPaths
            );
        } catch (\Exception $e) {
            return false; // TODO: `throw` custom \Exception $e & handle it smoothly on top-level.
        }

        /*
         * Preparing options
         */

        // Metadata (all, exif, icc, xmp or none (default))
        // Comma-separated list of existing metadata to copy from input to output
        $metadata = (
            $this->strip
            ? '-metadata none'
            : '-metadata all'
        );

        // lossless PNG conversion
        $lossless = (
            $this->extension == 'png'
            ? '-lossless'
            : ''
        );

        // Built-in method option
        $method = (
            defined('WEBPCONVERT_CWEBP_METHOD')
            ? ' -m ' . WEBPCONVERT_CWEBP_METHOD
            : ' -m 6'
        );

        // Built-in low memory option
        if (!defined('WEBPCONVERT_CWEBP_LOW_MEMORY')) {
            $lowMemory= '-low_memory';
        } else {
            $lowMemory = (
                WEBPCONVERT_CWEBP_LOW_MEMORY
                ? '-low_memory'
                : ''
            );
        }

        $optionsArray = [
            $metadata = $metadata,
            $quality = '-q ' . $this->quality,
            $lossless = $lossless,
            $method = $method,
            $lowMemory = $lowMemory,
            $input = $this->escapeFilename($this->source),
            $output = '-o ' . $this->escapeFilename($this->destination),
            $stderrRedirect = '2>&1'
        ];
        $options = implode(' ', $optionsArray);

        $nice = (
            $this->hasNiceSupport()
            ? 'nice'
            : ''
        );

        // Try all paths
        foreach ($binaries as $index => $binary) {
            $command = $nice . ' ' . $binary . ' ' . $options;
            exec($command, $output, $returnCode);
            var_dump($command);

            if ($returnCode == 0) { // Everything okay!
                // cwebp sets file permissions to 664 but instead ..
                // .. $destination's parent folder's permissions should be used (except executable bits)
                $destinationParent = dirname($this->destination);
                $fileStatistics = stat($destinationParent);

                // Apply same permissions as parent folder but strip off the executable bits
                $permissions = $fileStatistics['mode'] & 0000666;
                chmod($this->destination, $permissions);

                $success = true;
                break;
            }

            $success = false;
        }

        if (!$success) {
            return false;
        }

        return true;
    }
}
