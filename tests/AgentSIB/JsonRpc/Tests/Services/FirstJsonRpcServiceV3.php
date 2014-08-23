<?php


namespace AgentSIB\JsonRpc\Tests\Services;


class FirstJsonRpcServiceV3 extends FirstJsonRpcServiceV2{

    public function noParamsMethod ()
    {
        return parent::noParamsMethod().'_v3';
    }

    public function mathMethod ($one, $two, $three = 0, $four = 0)
    {
        return parent::mathMethod($one, $two, $three) + $four;
    }


} 