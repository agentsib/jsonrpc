<?php


namespace AgentSIB\JsonRpc\Tests\Services;


use AgentSIB\JsonRpc\JsonRpcServiceInterface;

class FirstJsonRpcService implements JsonRpcServiceInterface
{

    public function noParamsMethod()
    {
        return 'answer';
    }

    public function mathMethod($one, $two, $three = 0)
    {
        return $one - $two + $three;
    }

    public function errorMethod()
    {
        return 2/0;
    }
}
