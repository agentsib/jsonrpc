<?php


namespace AgentSIB\JsonRpc;


interface JsonRpcSerializerInterface
{

    /**
     * Parse request to stdClass object
     * @param $request
     * @return object
     */
    public function parseRequest($request);

    /**
     * Serialize request to some format
     * @param array $response
     * @return mixed
     */
    public function serializeResponse($response);
}
