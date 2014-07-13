<?php


namespace AgentSIB\JsonRpc;


use AgentSIB\JsonRpc\Requests\BatchJsonRpcRequest;
use AgentSIB\JsonRpc\Requests\JsonRpcRequest;
use AgentSIB\JsonRpc\Requests\NotificationJsonRpcRequest;
use AgentSIB\JsonRpc\Requests\SingleJsonRpcRequest;
use AgentSIB\JsonRpc\Response\BatchJsonRpcResponse;
use AgentSIB\JsonRpc\Response\SingleJsonRpcResponse;
use AgentSIB\JsonRpc\Transports\JsonRpcTransportInterface;

class JsonRpcClient
{
    const JSON_RPC_VERSION = '2.0';

    /** @var JsonRpcTransportInterface  */
    private $transport;

    public function __construct (JsonRpcTransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @param $method
     * @param null $params
     * @param null $id
     * @return SingleJsonRpcResponse|null
     */
    public function makeSingleRequest($method, $params = null, $requestId = null)
    {
        return $this->makeRequest(new SingleJsonRpcRequest($method, $params, $requestId));
    }

    /**
     * @param BatchJsonRpcRequest $request
     * @return BatchJsonRpcResponse|null
     */
    public function makeBatchRequest(BatchJsonRpcRequest $request)
    {
        return $this->makeRequest($request);
    }

    /**
     * @param $method
     * @param null $params
     * @codeCoverageIgnore
     */
    public function makeSingleNotification($method, $params = null)
    {

        $this->makeRequest(new NotificationJsonRpcRequest($method, $params));
    }

    public function makeRequest(JsonRpcRequest $request)
    {

        $response = $this->transport->sendRequest($request->serialize());

        if ($request instanceof SingleJsonRpcRequest) {
            return new SingleJsonRpcResponse($response);
        }
        if ($request instanceof BatchJsonRpcRequest) {
            return new BatchJsonRpcResponse($response);
        }
        return null;
    }
}
