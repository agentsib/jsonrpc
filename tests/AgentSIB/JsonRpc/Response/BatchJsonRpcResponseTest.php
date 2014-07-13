<?php


namespace AgentSIB\JsonRpc\Response;


use AgentSIB\JsonRpc\JsonRpcClient;

class BatchJsonRpcResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testBatchResponseCreate()
    {
        $request = json_encode(array(
            array(
                'jsonrpc'   =>  JsonRpcClient::JSON_RPC_VERSION,
                'result'    =>  'test',
                'id'        =>  'test'
            ),
            array(
                'jsonrpc'   =>  JsonRpcClient::JSON_RPC_VERSION,
                'result'    =>  'test1',
                'id'        =>  2
            )
        ));
        $response = new BatchJsonRpcResponse($request);

        $this->assertEquals($request, $response->getRawResponse());
        $this->assertTrue($response->hasResponseId(2));
        $this->assertTrue($response->hasResponseId('test'));
        $this->assertEquals('test1', $response->getResponseById(2)->getResult());
    }

    public function testBatchResponseNonExists()
    {
        $this->setExpectedException('Exception');
        $request = json_encode(array(
            array(
                'jsonrpc'   =>  JsonRpcClient::JSON_RPC_VERSION,
                'result'    =>  'test',
                'id'        =>  1
            ),
            array(
                'jsonrpc'   =>  JsonRpcClient::JSON_RPC_VERSION,
                'result'    =>  'test1',
                'id'        =>  2
            )
        ));
        $response = new BatchJsonRpcResponse($request);
        $response->getResponseById(552);
    }

    /**
     * @dataProvider testBatchResponseUncorrectProvider
     */
    public function testBatchResponseUncorrect($request)
    {
        $response = new BatchJsonRpcResponse($request);

        $this->assertEquals($request, $response->getRawResponse());
        $this->assertFalse($response->isCorrect());
    }

    public function testBatchResponseUncorrectProvider()
    {
        return array(
            array(json_encode(array(
                array(
                    'jsonrpc'   =>  JsonRpcClient::JSON_RPC_VERSION,
                    'result'    =>  'test',
                ),
                array(
                    'jsonrpc'   =>  JsonRpcClient::JSON_RPC_VERSION,
                    'result'    =>  'test1',
                    'id'        =>  2
                )
            ))),
            array(json_encode(array(
                array(
                    'jsonrpc'   =>  JsonRpcClient::JSON_RPC_VERSION,
                    'id'        =>  1
                ),
                array(
                    'jsonrpc'   =>  JsonRpcClient::JSON_RPC_VERSION,
                    'result'    =>  'test1',
                    'id'        =>  2
                )
            ))),
            array(json_encode(array(
                array(
                    'result'    =>  'test',
                    'id'        =>  1

                ),
                array(
                    'jsonrpc'   =>  JsonRpcClient::JSON_RPC_VERSION,
                    'result'    =>  'test1',
                    'id'        =>  2
                )
            ))),
        );
    }

    /**
     * @dataProvider testBatchResponseEmptyProvider
     */
    public function testBatchResponseEmpty($data)
    {
        $response = new BatchJsonRpcResponse($data);

        $this->assertTrue($response->isCorrect());
        $this->assertNull($response->getAllResponses());
    }

    public function testBatchResponseEmptyProvider()
    {
        return array(
            array(''),
            array(null),
        );
    }


    /**
     * @dataProvider testBatchResponseErrorsProvider
     */
    public function testBatchResponseErrors($data)
    {
        $response = new BatchJsonRpcResponse($data);

        $this->assertFalse($response->isCorrect());
    }

    public function testBatchResponseErrorsProvider()
    {
        return array(
            array('{}'),
            array('fadsfas'),
            array('{"test":"1"}'),
        );
    }
}
