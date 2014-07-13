<?php


namespace AgentSIB\JsonRpc\Requests;


use AgentSIB\JsonRpc\JsonRpcClient;

class NotificationJsonRpcRequest extends JsonRpcRequest
{
    private $method;
    private $params;

    public function __construct ($method, $params = null)
    {
        $this->method = trim($method);

        if (empty($this->method)) {
            throw new \Exception('Method is empty');
        }

        $this->params = $params;

    }

    /**
     * @return mixed
     */
    public function getMethod ()
    {
        return $this->method;
    }

    /**
     * @return mixed
     */
    public function getParams ()
    {
        return $this->params;
    }

    /**
     * @param mixed $params
     * @return $this
     */

    /**
     * Make array
     * @return mixed
     */
    public function serializeToArray ()
    {
        return array(
            'jsonrpc'   =>  JsonRpcClient::JSON_RPC_VERSION,
            'method'    =>  $this->method,
            'params'    =>  $this->params,
        );
    }
}
