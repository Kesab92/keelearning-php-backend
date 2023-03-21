<?php

namespace App\Push\Deepstream;

/**
 * A class representing a single API request.
 *
 * @author deepstreamHub GmbH <info@deepstreamhub.com>
 * @copyright (c) 2017, deepstreamHub GmbH
 */
class ApiRequest
{
    private $requestData;
    private $url;

    /**
     * Creates the request.
     *
     * @param string $url
     * @param mixed $authData
     */
    public function __construct($url, $authData)
    {
        $this->url = $url;
        $this->requestData = $authData;
        $this->requestData['body'] = [];
    }

    /**
     * Adds an aditional step to the request.
     *
     * @param array $request
     *
     * @private
     * @returns void
     */
    public function add($request)
    {
        array_push($this->requestData['body'], $request);
    }

    /**
     * Executes the HTTP request and parses the result.
     *
     * @private
     * @return mixed result data
     */
    public function execute()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->requestData, JSON_UNESCAPED_SLASHES));
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = [];
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            echo 'Error:'.$error;
            \Log::error($error);
        }
        curl_close($ch);

        if ($result === false) {
            return false;
        } else {
            return json_decode($result);
        }
    }
}
