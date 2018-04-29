<?php

namespace PHPWebP\Converters;

use PHPWebP\ConverterAbstract;

/**
 * Class Optimus
 *
 * Converts an image to WebP via Optimus.io by KeyCDN
 *
 * @package PHPWebP\Converters
 */
class Optimus extends ConverterAbstract
{
    public function checkRequirements()
    {
        if (!extension_loaded('curl')) {
            throw new \Exception('Required cURL extension is not available.');
        }

        if (!function_exists('curl_init')) {
            throw new \Exception('Required url_init() function is not available.');
        }

        return true;
    }

    public function convert()
    {
      // TODO: Check if PHPWEBP_OPTIMUS_KEY was even set, otherwise exit with Exception
        try {
            $this->checkRequirements();

            // Initializing cURL, setting response headers & requesting image conversion
            $url = 'https://api.optimus.io/' . PHPWEBP_OPTIMUS_KEY . '?webp';
            $headers = [
                'User-Agent: Optimus-API',
                'Accept: image/*'
            ];
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_POSTFIELDS => file_get_contents($this->source),
                CURLOPT_BINARYTRANSFER => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
                CURLOPT_SSL_VERIFYPEER => true
            ]);
            $response = curl_exec($ch);
            $curlError = curl_error($ch);

            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $result = substr($response, $header_size);

            if (!empty($curlError) || empty($result)) {
                throw new \Exception('cURL failed: ' . $curlError . ' Output: ' . $body);
            }
        } catch (\Exception $e) {
            return false; // TODO: `throw` custom \Exception $e & handle it smoothly on top-level.
        }

        $success = file_put_contents($this->destination, $result);

        if (!$success) {
            return false;
        }

        return true;
    }
}
