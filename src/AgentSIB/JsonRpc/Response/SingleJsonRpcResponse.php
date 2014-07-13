<?php


namespace AgentSIB\JsonRpc\Response;


use AgentSIB\JsonRpc\JsonRpcClient;

class SingleJsonRpcResponse extends JsonRpcResponse
{
    private $id = false;
    private $result = false;
    private $error_code = false;
    private $error_message = false;
    private $error_data = false;

    protected function parseResponse($response)
    {
        if ($this->validateResponse($response)) {
            $this->id = $response->id;
            if (property_exists($response, 'result')) {
                $this->result = $response->result;
                return true;
            } elseif (property_exists($response, 'error')) {
                $this->error_code = $response->error->code;
                $this->error_message = $response->error->message;
                if (property_exists($response->error, 'data')) {
                    $this->error_data = $response->error->data;
                }
                return true;
            }
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getErrorCode ()
    {
        return $this->error_code;
    }

    /**
     * @return mixed
     */
    public function getErrorData ()
    {
        return $this->error_data;
    }

    /**
     * @return mixed
     */
    public function getErrorMessage ()
    {
        return $this->error_message;
    }

    /**
     * @return mixed
     */
    public function getId ()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getResult ()
    {
        return $this->result;
    }

    public function isSuccess()
    {
        return $this->error_code === false;
    }

    public function isError()
    {
        return !$this->isSuccess();
    }


    public function validateResponse($response)
    {
        if (
            empty($response) ||
            !is_object($response) ||
            !property_exists($response, 'jsonrpc') ||
            !property_exists($response, 'id') ||
            $response->jsonrpc !== JsonRpcClient::JSON_RPC_VERSION
        ) {
            return false;
        }
        return true;
    }
}
