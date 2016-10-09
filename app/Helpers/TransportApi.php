<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class TransportApi
{
    /**
     * Base uri for transport api
     */
    const BASE_URI = 'http://transportapi.com/v3/';

    /**
     * @var $appID
     */
    protected  $appId;

    /**
     * @var $apiKey
     */
    protected $apiKey;

    /**
     * @var $client
     */
    protected $client;

    /**
     * @var RequestException $error
     */
    protected $error;

    /**
     * Class constructor
     * @param $config
     */
    public function __construct($config)
    {
        $this->apiKey = $config['api_key'];
        $this->appId = $config['app_id'];
        $this->client = new Client(array('base_uri' => $this->getBaseUri()));
    }

    /**
     * Gets live timetable for specific bus stop
     * @param $atcoCode
     * @return decoded json
     */
    public function getLiveTimeTable($atcoCode)
    {
        try {
            // Send request
            $response = $this->client->request(
                'GET', 'uk/bus/stop/' . $atcoCode . '/live.json',
                array('query' => 'app_id=' . $this->appId . '&app_key=' . $this->apiKey . '&group=route&nextbuses=yes')
            );
        } catch (RequestException $e) {
            $this->error .= Psr7\str($e->getRequest());
            if ($e->hasResponse()) {
                $this->error .= Psr7\str($e->getResponse());
            }
            return $this->error;
        }

        return $this->getJsonString($response);
    }

    /**
     * Retrieve 10 stops closest to the given location
     * @param $latitude
     * @param $longitude
     * @return RequestException|string
     */
    public function getNearbyStops($latitude, $longitude)
    {
        try {
            // Send request
            $response = $this->client->request(
                'GET', 'uk/bus/stops/near.json',
                array('query' => 'app_id=' . $this->appId . '&app_key=' . $this->apiKey . '&lat=' . $latitude . '&lon=' . $longitude . '&rpp=10')
            );
        } catch (RequestException $e) {
            $this->error .= Psr7\str($e->getRequest());
            if ($e->hasResponse()) {
                $this->error .= Psr7\str($e->getResponse());
            }
            return $this->error;
        }

        return $this->getJsonString($response);
    }

    /**
     * Returns Base URI to be used with api
     * @return string
     */
    protected function getBaseUri()
    {
        return self::BASE_URI;
    }

    /**
     * Return the request body as a json string
     * @param $response
     * @return string
     */
    protected function getJsonString($response)
    {
        $body= $response->getBody();
        return (string)$body;
    }
}