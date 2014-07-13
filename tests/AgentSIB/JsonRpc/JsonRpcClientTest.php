<?php


namespace AgentSIB\JsonRpc;


use AgentSIB\JsonRpc\Requests\BatchJsonRpcRequest;
use AgentSIB\JsonRpc\Requests\NotificationJsonRpcRequest;
use AgentSIB\JsonRpc\Requests\SingleJsonRpcRequest;
use AgentSIB\JsonRpc\Transports\InternalJsonRpcTransport;

class JsonRpcClientTest extends \PHPUnit_Framework_TestCase
{

    /** @var  JsonRpcClient */
    private $client;

    /**
     * @dataProvider testSingleRequestProvider
     */
    public function testSingleRequest($method, $params, $is_correct, $result, $error_code, $error_data)
    {

        $response = $this->client->makeSingleRequest($method, $params);

        if ($is_correct) {
            $this->assertTrue($response->isCorrect());
            if (!is_null($result)) {
                $this->assertTrue($response->isSuccess());
                $this->assertFalse($response->getErrorCode());
                $this->assertFalse($response->getErrorData());
                $this->assertFalse($response->getErrorMessage());
                $this->assertEquals($result, $response->getResult());
            } elseif (!is_null($error_code)) {
                $this->assertTrue($response->isError());
                $this->assertFalse($response->getResult());
                $this->assertNotFalse($response->getErrorMessage());
                $this->assertNotFalse($response->getErrorCode());
                if (is_null($error_data)) {
                    $this->assertFalse($response->getErrorData());
                }
            } else {
                $this->fail('Test not uncorrect');
            }
        } else {
            $this->fail('Response not currect');
        }
    }


    public function testSingleRequestProvider()
    {
        $error = '';
        try {
            2/0;
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
        return array(
            array('noParamsMethod', null, true, 'answer', null, null),
            array('noParamsMethod', array(1), true, 'answer', null, null),
            array('mathMethod', array(5, 10), true, 5 - 10, null, null),
            array('mathMethod', array(10, 5), true, 10 - 5, null, null),
            array('mathMethod', array(10, 5, 3), true, 10 - 5 + 3, null, null),
            array('mathMethod', array('one' =>  10, 'two' => 5), true, 10 - 5, null, null),
            array('mathMethod', array('two' => 5, 'one' =>  10), true, 10 - 5, null, null),
            array('mathMethod', array('two' => 5, 'three' => 3, 'one' =>  10), true, 10 - 5 + 3, null, null),
            array('mathMethod', array(
                'four'=> 33, 'two' => 5, 'three' => 3, 'one' =>  10), true, 10 - 5 + 3, null, null
            ),
            //Errors
            array('noParamsMethod', 1, true, null, JsonRpcException::ERROR_INVALID_REQUEST, null),
            array('noParamsMethod', 'asdaf', true, null, JsonRpcException::ERROR_INVALID_REQUEST, null),
            array('mathMethod', array(1), true, null, JsonRpcException::ERROR_INVALID_PARAMS, null),
            array('mathMethod', array(
                'two' => 5, 'four'=> 33), true, null, JsonRpcException::ERROR_INVALID_PARAMS, null
            ),
            array('notExistsMethod', array(1), true, null, JsonRpcException::ERROR_METHOD_NOT_FOUND, null),
            array('errorMethod', array(), true, null, JsonRpcException::ERROR_INTERNAL_ERROR, $error),

            array(2, null, true, null, JsonRpcException::ERROR_INVALID_PARAMS, null),

        );
    }


    /**
     * @dataProvider testNotificationRequestProvider
     */
    public function testNotificationRequest($method, $params)
    {
        $response = $this->client->makeRequest(new NotificationJsonRpcRequest($method, $params));

        $this->assertNull($response);
    }

    public function testNotificationRequestProvider()
    {
        return array(
            array('noParamsMethod', null),
            array('noParamsMethod', 1), // Wrong params
            array('noParamsMethod', 'asdf'), //Wrong params
            array('mathMethod', null), // No params
            array('mathMethod', array(1,2)), // Correct
            array('nonExistsMethod', null),
            array(2, null), //error
            array('errorMethod', null), //error
        );
    }

    /**
     * @dataProvider testMethodIsEmptyProvider
     */
    public function testMethodIsEmpty($method, $params)
    {
        $this->setExpectedException('Exception');
        $this->client->makeRequest(new NotificationJsonRpcRequest($method, $params));
    }

    public function testMethodIsEmptyProvider()
    {
        return array(
            array(null, null), //error
            array('', null), //error
        );
    }

    public function testBatchRequestEmptyId()
    {
        $this->setExpectedException('Exception');
        BatchJsonRpcRequest::create()
            ->addRequest(new SingleJsonRpcRequest('noParamsMethod'));
    }


    public function testBatchRequestDublId()
    {
        $this->setExpectedException('Exception');
        BatchJsonRpcRequest::create()
            ->addRequest(new SingleJsonRpcRequest('noParamsMethod', null, 1))
            ->addRequest(new SingleJsonRpcRequest('noParamsMethod', null, 1));
    }

    public function testBatchRequestWrongTypeId()
    {
        $this->setExpectedException('Exception');
        BatchJsonRpcRequest::create()
            ->addRequest(BatchJsonRpcRequest::create()->addRequest(new SingleJsonRpcRequest('test', null, 1)));
    }

    public function testBatchRequestEmpty()
    {
        $this->setExpectedException('Exception');
        $this->client->makeBatchRequest(BatchJsonRpcRequest::create());
    }

    public function testBatchRequest()
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


        $response = $this->client->makeBatchRequest($request);

        $rows = $response->getAllResponses();

        $this->assertCount(6, $rows);

        $errormethod = $response->getResponseById('errormethod');

        $this->assertTrue($errormethod->isError());
        $this->assertNotFalse($errormethod->getErrorData());
        $this->assertEquals(JsonRpcException::ERROR_INTERNAL_ERROR, $errormethod->getErrorCode());

        $method = $response->getResponseById('1');
        $this->assertTrue($method->isSuccess());
        $this->assertEquals('answer', $method->getResult());


        $method = $response->getResponseById('4');
        $this->assertTrue($method->isSuccess());
        $this->assertEquals(10 - 5, $method->getResult());


        $method = $response->getResponseById('errormethod');
        $this->assertTrue($method->isError());
        $this->assertEquals(JsonRpcException::ERROR_INTERNAL_ERROR, $method->getErrorCode());
        $this->assertNotEmpty($method->getErrorData());
    }

    protected function setUp ()
    {
        if (!$this->client) {
            $server = new JsonRpcServer(new BaseJsonRpcSerializer());
            $server->addService(JsonRpcServer::DEFAULT_NAMESPACE, '\\AgentSIB\\JsonRpc\\Services\\FirstJsonRpcService');
            $this->client = new JsonRpcClient(new InternalJsonRpcTransport($server));
        }
    }
}
