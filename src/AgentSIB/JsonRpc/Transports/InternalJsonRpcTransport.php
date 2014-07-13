<?php


namespace AgentSIB\JsonRpc\Transports;


use AgentSIB\JsonRpc\JsonRpcServer;

class InternalJsonRpcTransport implements JsonRpcTransportInterface
{

    private $server;

    public function __construct (JsonRpcServer $server)
    {
        $this->server = $server;
    }


    public function sendRequest ($request)
    {
        return $this->server->process($request);
    }
}
