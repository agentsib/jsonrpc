<?php


namespace AgentSIB\JsonRpc\Services;


use AgentSIB\JsonRpc\JsonRpcServiceInterface;

class SecondJsonRpcService implements JsonRpcServiceInterface
{

    public function testMethod()
    {
        return 'answer';
    }

    private function privateMethod()
    {
        return 'test';
    }

    protected function protectedMethod()
    {
        return 'test';
    }

    function defaultMethod()
    {
        return 'test';
    }

    public static function staticMethod()
    {
        return 'test';
    }
}
