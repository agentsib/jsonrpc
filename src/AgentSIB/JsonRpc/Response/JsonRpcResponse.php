<?php


namespace AgentSIB\JsonRpc\Response;


abstract class JsonRpcResponse
{

    protected $raw_response;
    protected $json_response;

    protected $state;

    public function __construct ($response)
    {
        $this->raw_response = $response;

        if (empty($this->raw_response)) {
            $this->state = true;
        } else {
            $this->json_response = @json_decode($this->raw_response, false, 32);
            $this->state = $this->parseResponse($this->json_response);
        }
    }

    public function isCorrect()
    {
        return $this->state;
    }

    public function getRawResponse()
    {
        return $this->raw_response;
    }

    abstract protected function parseResponse($response);
}
