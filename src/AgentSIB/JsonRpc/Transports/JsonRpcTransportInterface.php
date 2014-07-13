<?php


namespace AgentSIB\JsonRpc\Transports;


interface JsonRpcTransportInterface
{
    public function sendRequest($request);
}
