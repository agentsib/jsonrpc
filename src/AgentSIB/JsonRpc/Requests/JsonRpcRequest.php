<?php


namespace AgentSIB\JsonRpc\Requests;


abstract class JsonRpcRequest
{
    /**
     * Serialize to jsonrpc object
     * @return string
     */
    public function serialize()
    {
        return json_encode($this->serializeToArray());
    }

    /**
     * Make array
     * @return mixed
     */
    abstract public function serializeToArray();
}
