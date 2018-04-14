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

        if (!function_exists('curl_file_create')) {
            throw new \Exception('Required curl_file_create() function is not available (requires PHP > 5.5).');
        }

        return true;
    }

    public function convertImage()
    {
        try {
            $this->checkRequirements();

            // Initializing cURL, setting response headers & requesting image conversion
            $curl = new \Curl\Curl();
            $curl->setHeader('User-Agent: Optimus-API', 'Accept: image/*');
            $result = $curl->post('https://api.optimus.io/' . WEBPCONVERT_OPTIMUS_KEY . '?webp', [
                'file' => curl_file_create($this->source)
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
