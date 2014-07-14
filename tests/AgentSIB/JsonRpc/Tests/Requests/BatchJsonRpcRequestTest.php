<?php


namespace AgentSIB\JsonRpc\Tests\Requests;


use AgentSIB\JsonRpc\Requests\BatchJsonRpcRequest;
use AgentSIB\JsonRpc\Requests\NotificationJsonRpcRequest;
use AgentSIB\JsonRpc\Requests\SingleJsonRpcRequest;

class BatchJsonRpcRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testBatchJsonRpcRequest()
    {
        $request = BatchJsonRpcRequest::create()
            ->addRequest(new SingleJsonRpcRequest('noParamsMethod', null, 1))
            ->addRequest(new SingleJsonRpcRequest('noParamsMethod', 'ssdf', 2))
            ->addRequest(new SingleJsonRpcRequest('mathMethod', null, 3))
            ->addRequest(new NotificationJsonRpcRequest('mathMethod', array(1,2)))
            ->addRequest(new SingleJsonRpcRequest('mathMethod', array(10, 5), 4))
            ->addRequest(new NotificationJsonRpcRequest('notExistsMethod', array(1,2)))
            ->addRequest(new SingleJsonRpcRequest('notExistsMethod', array(1,4), 'nonexist'))
            ->addRequest(new SingleJsonRpcRequest('errorMethod', null, 'errormethod'));

        // Test for BatchJsonRequest::getRequestById
        $req = $request->getRequestById('nonexist');
        $this->assertEquals('nonexist', $req->getId());
        $this->assertEquals('notExistsMethod', $req->getMethod());
        $this->assertEquals(array(1,4), $req->getParams());


        $req = $request->getRequestById('asdf');
        $this->assertNull($req);
        // End
    }
}
