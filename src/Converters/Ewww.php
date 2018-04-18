<?php

namespace WebPConvert\Converters;

use WebPConvert\ConverterAbstract;

/**
 * Class Ewww
 *
 * Converts an image to WebP via EWWW Online Image Optimizer
 *
 * @package WebPConvert\Converters
 */
class Ewww extends ConverterAbstract
{
    public function checkRequirements()
    {
        if (!extension_loaded('curl')) {
            throw new \Exception('Required cURL extension is not available.');
        }

        if (!function_exists('curl_init')) {
            throw new \Exception('Required url_init() function is not available.');
        }

        if (!defined("WEBPCONVERT_EWWW_KEY")) {
            throw new \Exception('Missing API key.');
        }

        if (!function_exists('curl_file_create')) {
            throw new \Exception('Required curl_file_create() function is not available (requires PHP > 5.5).');
        }

        return true;
    }

    // Throws an exception if the provided API key is invalid
    // TODO: Move to ConverterAbstract when other cloud services are added (Optimus currently does NOT)
    public function isValidKey($key = WEBPCONVERT_EWWW_KEY)
    {
        try {
            $curl = new \Curl\Curl();
            $result = $curl->post('https://optimize.exactlywww.com/verify/', [
                'api_key' => $key
            ]);

            if ($curl->error) {
                throw new \Exception($curl->errorMessage . ' - ' . $curl->errorCode);
            }
        } catch (\Exception $e) {
            return false; // TODO: Throw $e so error may be inspected later
        }

        /*
         * There are three possible responses:
         * 'great' = verification successful
         * 'exceeded' = indicates a valid key with no remaining image credits
         * '' = an empty response indicates that the key is not valid
        */

        $success = (bool) preg_match('/great/', $result);

        if (!$success && preg_match('/exceeded/', $result)) {
            throw new \Exception('API key is valid, but has no remaining image credits.');
        }

        return $success;
    }

    public function convert()
    {
        try {
            $this->checkRequirements();

            // Checking if provided key is valid
            $this->isValidKey();

            // Initializing cURL, setting response headers & requesting image conversion
            $curl = new \Curl\Curl();
            $curl->setHeader('User-Agent: WebPConvert', 'Accept: image/*');
            $result = $curl->post('https://optimize.exactlywww.com/v2/', [
                'api_key' => WEBPCONVERT_EWWW_KEY,
                'webp' => '1',
                'file' => curl_file_create($this->source),
                'quality' => $this->quality,
                'metadata' => ($this->strip ? '0' : '1')
            ]);

            if ($curl->error) {
                throw new \Exception($curl->errorMessage . ' - ' . $curl->errorCode);
            }
        } catch (\Exception $e) {
            return false; // TODO: `throw` custom \Exception $e & handle it smoothly on top-level.
        }

        // TODO: Remove header data from image (substr($curl->responseHeaders, $result))
        $success = file_put_contents($this->destination, $result);

        if (!$success) {
            return false;
        }

        return true;
    }
}
