<?php


namespace AgentSIB\JsonRpc\Tests\Services;


use AgentSIB\JsonRpc\JsonRpcServiceInterface;

class ThirdJsonRpcServiceV2 implements JsonRpcServiceInterface
{
    public function noParamsMethod()
    {
        return 'third_answer';
    }
}
