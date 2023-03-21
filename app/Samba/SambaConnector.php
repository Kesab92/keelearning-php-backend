<?php

namespace App\Samba;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use function GuzzleHttp\Promise\all;
use Illuminate\Support\Collection;

/**
 * This class is used by the Samba class to talk to the Samba API.
 */
class SambaConnector
{
    /**
     * @var Client
     */
    private Client $client;

    /**
     * @var array
     */
    private array $options;

    /**
     * @var string
     */
    private $endpoint;

    public function __construct()
    {
        $this->client = new Client();
        $this->options = [
            'headers' => [
                'Authorization' => '', // Is being set in resetAuth()
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];
        $this->resetAuth();
    }

    /**
     * Use a specific token
     * We need this for some APIs because samba doesn't allow us to use them via the admin account.
     *
     * @param string $token
     */
    public function withAppSpecificAuth($token)
    {
        $this->options['headers']['Authorization'] = 'Basic '.$token;
        $this->endpoint = config('services.samba.endpoint').$this->getUsernameFromToken($token);
    }

    /**
     * Set up the authentication to use our default admin account.
     */
    public function resetAuth()
    {
        $adminToken = config('services.samba.token');
        $this->options['headers']['Authorization'] = 'Basic '.$adminToken;
        $this->endpoint = config('services.samba.endpoint').$this->getUsernameFromToken($adminToken);
    }

    /**
     * Extracts the samba username from the given token.
     *
     * @param $token
     * @return string
     */
    private function getUsernameFromToken($token)
    {
        // The token is base64encoded username:password
        $data = explode(':', base64_decode($token));

        return $data[0];
    }

    /**
     * Executes a GET request.
     *
     * @param $path
     * @return array
     * @throws SambaConnectionException
     */
    public function get($path)
    {
        return $this->makeRequest('GET', $path);
    }

    public function getMultiple(Collection $paths)
    {
        $options = $this->options;
        $paths->transform(function ($path) use ($options) {
            $url = $this->getUrl($path);

            return $this->client->requestAsync('GET', $url, $options);
        });
        $responses = all($paths)->then(function ($res) {
            $content = $res->getBody()->getContents();

            // Check if the request was unsuccessful
            if ($res->getStatusCode() >= 300) {
                throw new SambaConnectionException($content, $res->getStatusCode());
            }

            // Try to json decode the content
            if ($content) {
                try {
                    return json_decode($content, true);
                } catch (\Exception $e) {
                    throw new SambaConnectionException('Could not decode result');
                }
            }
            throw new SambaConnectionException('Did not receive a result');
        })->wait();

        return $responses;
    }

    /**
     * Executes a PUT request
     * Samba uses PUT requests to create new objects.
     *
     * @param $path
     * @param $data
     * @return array
     * @throws SambaConnectionException
     */
    public function put($path, $data)
    {
        $options = [
            'body' => 'input_type=json&rest_data='.json_encode($data),
        ];

        return $this->makeRequest('PUT', $path, $options);
    }

    /**
     * Executes a DELETE request
     * Samba uses DELETE requests to remove objects.
     *
     * @param $path
     * @param $data
     * @return array
     * @throws SambaConnectionException
     */
    public function delete($path, $data)
    {
        $options = [
            'body' => 'input_type=json&rest_data='.json_encode($data),
        ];

        return $this->makeRequest('DELETE', $path, $options);
    }

    /**
     * Executes a POST request
     * Samba uses POST requests to update existing objects.
     *
     * @param $path
     * @param $data
     * @return array
     * @throws SambaConnectionException
     */
    public function post($path, $data)
    {
        $options = [
            'body' => 'input_type=json&rest_data='.json_encode($data),
        ];

        return $this->makeRequest('POST', $path, $options);
    }

    /**
     * @param $method
     * @param $path
     * @param array $options
     * @return array
     * @throws SambaConnectionException
     */
    private function makeRequest($method, $path, $options = [])
    {
        $url = $this->getUrl($path);
        $options = array_merge($this->options, $options);
        try {
            $res = $this->client->request($method, $url, $options);
        } catch (RequestException $exception) {
            // Samba treats 404s as no content
            if ($exception->getResponse()->getStatusCode() === 404) {
                return [];
            }
            throw $exception;
        }
        $content = $res->getBody()->getContents();

        // Check if the request was unsuccessful
        if ($res->getStatusCode() >= 300) {
            throw new SambaConnectionException($content, $res->getStatusCode());
        }

        // Try to json decode the content
        if ($content) {
            try {
                $content = json_decode($content, true);
            } catch (\Exception $e) {
            }
        }

        return $content;
    }

    /**
     * Returns the full samba URL for the given path.
     *
     * @param $path
     * @return string
     */
    private function getUrl($path)
    {
        return $this->endpoint.$path;
    }
}
