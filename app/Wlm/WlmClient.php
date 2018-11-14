<?php

namespace App\Wlm;

use GuzzleHttp\Client;

class WlmClient implements WlmClientInterface
{
    public $response;
    public $statusCode;
    public $responseMessage;
    public $responseCode;
    protected $client;
    protected $wlmStaff;
    protected $baseUri;

    public function __construct()
    {
        $this->client = new Client;
        $this->wlmStaff = collect([]);
        $this->baseUri = config('exampapers.wlm_uri');
    }

    protected function get($url)
    {
        return $this->client->get($url);
    }

    public function getData($endpoint)
    {
        $this->response = $this->get($this->baseUri . $endpoint);
        $this->statusCode = $this->response->getStatusCode();
        $json = json_decode($this->response->getBody(), true);
        if (!array_key_exists('Data', $json)) {
            return collect([]);
        }
        $this->responseMessage = $json['Response'];
        $this->responseCode = $json['ResponseCode'];
        return collect($json['Data']);
    }

    public function getCourses()
    {
        return $this->getData('getcourse/all');
    }

    public function getCourse($code)
    {
        return $this->getData("getcourse/{$code}");
    }

    public function getStaff($guid)
    {
        if (!$this->wlmStaff->has($guid)) {
            $this->wlmStaff[$guid] = $this->getData("getdetails/{$guid}");
        }
        return $this->wlmStaff[$guid];
    }
}
