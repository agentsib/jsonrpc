<?php


namespace AgentSIB\JsonRpc\Tests;

use AgentSIB\JsonRpc\JsonRpcException;
use AgentSIB\JsonRpc\Serializers\BaseJsonRpcSerializer;
use AgentSIB\JsonRpc\JsonRpcServer;

class JsonRpcServerTest extends \PHPUnit_Framework_TestCase
{

    /** @var  JsonRpcServer */
    private $server;

    /**
     * @dataProvider testAddServiceProvider
     * @expectedException
     */
    public function testAddServices($namespace, $class, $isError)
    {
        if ($isError) {
            $this->setExpectedException('Exception');
        }

        $this->server->addService($namespace, $class);

        $this->server = null;
    }

    public function testAddServiceProvider()
    {
        return array(
            array('newns', '\\AgentSIB\\JsonRpc\\Tests\\Services\\FirstJsonRpcService', false),
            array('second', '\\AgentSIB\\JsonRpc\\Tests\\Services\\FirstJsonRpcService', true),
            array('SeCond', '\\AgentSIB\\JsonRpc\\Tests\\Services\\FirstJsonRpcService', true),
            array('Wrong NS', '\\AgentSIB\\JsonRpc\\Tests\\Services\\FirstJsonRpcService', true),
            array('Wrong1', '\\AgentSIB\\JsonRpc\\Tests\\Services\\FirstJsonRpcService', true),
            array('newns', '\\AgentSIB\\JsonRpc\\Tests\\Services\\FirstJsonRpcServiceasdf', true),
        );
    }

    /**
     * @dataProvider testInvalidJsonProvider
     */
    public function testInvalidJson($data)
    {
        $result = $this->makeSingleJsonRpcErrorRequest($data);

        $this->assertNull($result->id);
        $this->assertEquals(JsonRpcException::ERROR_PARSE_ERROR, $result->error->code);
    }

    public function testInvalidJsonProvider()
    {
        return array(
            array('{"jsonrpc": "2.0", "method": "foobar, "params": "bar", "baz]'),
            array(''),
            array(34),
            array('[]'),
            array(null)
        );
    }

    /**
     * @dataProvider testInvalidRequestProvider
     */
    public function testInvalidRequest($data, $requestId)
    {
        $result = $this->makeSingleJsonRpcErrorRequest($data);

        if (is_null($requestId)) {
            $this->assertNull($result->id);
        } else {
            $this->assertEquals($requestId, $result->id);
        }
        $this->assertEquals(JsonRpcException::ERROR_INVALID_REQUEST, $result->error->code);
    }

    public function testInvalidRequestProvider()
    {
        return array(
            array('{"jsonrpc": "2.0", "method": "", "params": null, "id":"1"}' , 1),
            array('{"jsonrpc": "2.0", "method": 1, "id":"1"}' , 1),
            array('{"jsonrpc": "2.0", "params": "bar", "id":"2"}', 2),
            array('{"method": 1, "params": "bar", "id":"3"}', 3),
            array('{"jsonrpc": "2.1", "method": 1, "params": "bar", "id":"sdf"}',"sdf"),
        );
    }

    /**
     * @dataProvider testNamespacesProvider
     */
    public function testNamespaces($data, $isError)
    {
        $request = $this->createSingleRequest($data['method'], $data['params'], $data['id']);

        if ($isError) {
            $result = $this->makeSingleJsonRpcErrorRequest($request);
            $this->assertEquals(JsonRpcException::ERROR_METHOD_NOT_FOUND, $result->error->code);
        } else {
            $result = $this->makeSingleJsonRpcRequest($request);
        }

        $this->assertEquals($data['id'], $result->id);
    }

    public function testNamespacesProvider()
    {
        return array(
            array(
                array(
                    'method'    =>  'noParamsMethod',
                    'params'    =>  null,
                    'id'        =>  1
                ),
                false
            ),
            array(
                array(
                    'method'    =>  'second.testMethod',
                    'params'    =>  null,
                    'id'        =>  1
                ),
                false
            ),
            array(
                array(
                    'method'    =>  'Second.testMethod',
                    'params'    =>  null,
                    'id'        =>  1
                ),
                false
            ),
            array(
                array(
                    'method'    =>  'second.testmethod',
                    'params'    =>  null,
                    'id'        =>  1
                ),
                false
            ),
            array(
                array(
                    'method'    =>  '.noParamsMethod',
                    'params'    =>  null,
                    'id'        =>  1
                ),
                true
            ),
            array(
                array(
                    'method'    =>  'second.',
                    'params'    =>  null,
                    'id'        =>  1
                ),
                true
            ),
            array(
                array(
                    'method'    =>  'second.notExistsMethod',
                    'params'    =>  null,
                    'id'        =>  1
                ),
                true
            ),
            array(
                array(
                    'method'    =>  'namespace.notExistsMethod',
                    'params'    =>  null,
                    'id'        =>  1
                ),
                true
            ),
        );
    }

    /**
     * @dataProvider testMethodsExistsProvider
     */
    public function testMethodsExists($data, $error = null)
    {
        $request = $this->createSingleRequest($data['method'], $data['params'], $data['id']);

        if (is_null($error)) {
            $result = $this->makeSingleJsonRpcRequest($request);
        } else {
            $result = $this->makeSingleJsonRpcErrorRequest($request);
            $this->assertEquals($error, $result->error->code);
        }

        $this->assertEquals($data['id'], $result->id);
    }

    public function testMethodsExistsProvider()
    {
        return array(
            array(
                array(
                    'method'    =>  'second.testMethod',
                    'params'    =>  null,
                    'id'        =>  1
                ),
                null
            ),
            array(
                array(
                    'method'    =>  'second.privateMethod',
                    'params'    =>  null,
                    'id'        =>  1
                ),
                JsonRpcException::ERROR_METHOD_NOT_FOUND
            ),
            array(
                array(
                    'method'    =>  'second.protectedMethod',
                    'params'    =>  null,
                    'id'        =>  1
                ),
                JsonRpcException::ERROR_METHOD_NOT_FOUND
            ),
            array(
                array(
                    'method'    =>  'second.defaultMethod',
                    'params'    =>  null,
                    'id'        =>  1
                ),
                null
            ),
            array(
                array(
                    'method'    =>  'second.staticMethod',
                    'params'    =>  null,
                    'id'        =>  1
                ),
                JsonRpcException::ERROR_METHOD_NOT_FOUND
            ),

        );
    }

    /**
     * @dataProvider testMethodWithoutParamsProvider
     */
    public function testMethodWithoutParams($data, $error)
    {
        $request = $this->createSingleRequest($data['method'], $data['params'], $data['id']);

        if (is_null($error)) {
            $result = $this->makeSingleJsonRpcRequest($request);
        } else {
            $result = $this->makeSingleJsonRpcErrorRequest($request);
            $this->assertEquals($error, $result->error->code);
        }
        $this->assertEquals($data['id'], $result->id);
    }

    public function testMethodWithoutParamsProvider()
    {
        return array(
            array(
                array(
                    'method'    =>  'noParamsMethod',
                    'params'    =>  'asdf',
                    'id'        =>  1
                ),
                JsonRpcException::ERROR_INVALID_PARAMS
            ),
            array(
                array(
                    'method'    =>  'noParamsMethod',
                    'params'    =>  123,
                    'id'        =>  1
                ),
                JsonRpcException::ERROR_INVALID_PARAMS
            ),
            array(
                array(
                    'method'    =>  'noParamsMethod',
                    'params'    =>  null,
                    'id'        =>  1
                ),
                null
            ),
            array(
                array(
                    'method'    =>  'noParamsMethod',
                    'params'    =>  array(),
                    'id'        =>  1
                ),
                null
            ),
            array(
                array(
                    'method'    =>  'noParamsMethod',
                    'params'    =>  array(1,2,3,4),
                    'id'        =>  1
                ),
                null
            ),
            array(
                array(
                    'method'    =>  'noParamsMethod',
                    'params'    =>  array(
                        'param1' =>  1,
                        'param2' =>  2,
                    ),
                    'id'        =>  1
                ),
                null
            ),
        );
    }

    /**
     * @dataProvider testMethodWithParamsProvider
     */
    public function testMethodWithParams($data, $res, $error)
    {
        $request = $this->createSingleRequest($data['method'], $data['params'], $data['id']);

        if (is_null($error)) {
            $result = $this->makeSingleJsonRpcRequest($request);
            $this->assertEquals($res, $result->result);
        } else {
            $result = $this->makeSingleJsonRpcErrorRequest($request);
            $this->assertEquals($error, $result->error->code);
        }
        $this->assertEquals($data['id'], $result->id);
    }

    public function testMethodWithParamsProvider()
    {
        return array(
            array(
                array(
                    'method'    =>  'mathMethod',
                    'params'    =>  array(10, 5),
                    'id'        =>  1
                ),
                10 - 5,
                null
            ),
            array(
                array(
                    'method'    =>  'mathMethod',
                    'params'    =>  array(5, 10),
                    'id'        =>  1
                ),
                5 - 10,
                null
            ),
            array(
                array(
                    'method'    =>  'mathMethod',
                    'params'    =>  array(5, 10, 2),
                    'id'        =>  1
                ),
                5 - 10 + 2,
                null
            ),
            array(
                array(
                    'method'    =>  'mathMethod',
                    'params'    =>  array(5, 10, 2, 23, 54),
                    'id'        =>  1
                ),
                5 - 10 + 2,
                null
            ),
            array(
                array(
                    'method'    =>  'mathMethod',
                    'params'    =>  array(5),
                    'id'        =>  1
                ),
                0,
                JsonRpcException::ERROR_INVALID_PARAMS
            ),
            array(
                array(
                    'method'    =>  'mathMethod',
                    'params'    =>  array(),
                    'id'        =>  1
                ),
                0,
                JsonRpcException::ERROR_INVALID_PARAMS
            ),
            array(
                array(
                    'method'    =>  'mathMethod',
                    'params'    =>  null,
                    'id'        =>  1
                ),
                0,
                JsonRpcException::ERROR_INVALID_PARAMS
            ),
            array(
                array(
                    'method'    =>  'mathMethod',
                    'params'    =>  array(
                        'one'   =>  10,
                        'three' =>  3
                    ),
                    'id'        =>  1
                ),
                0,
                JsonRpcException::ERROR_INVALID_PARAMS
            ),
            array(
                array(
                    'method'    =>  'mathMethod',
                    'params'    =>  array(
                        'one'   =>  10,
                        'two'   =>  5
                    ),
                    'id'        =>  1
                ),
                10 - 5,
                null
            ),
            array(
                array(
                    'method'    =>  'mathMethod',
                    'params'    =>  array(
                        'two'   =>  5,
                        'one'   =>  10,
                    ),
                    'id'        =>  1
                ),
                10 - 5,
                null
            ),
            array(
                array(
                    'method'    =>  'mathMethod',
                    'params'    =>  array(
                        'three' =>  3,
                        'two'   =>  5,
                        'one'   =>  10,
                    ),
                    'id'        =>  1
                ),
                10 - 5 + 3,
                null
            ),
        );
    }

    public function testSingleNotification()
    {
        $request = $this->createSingleNotificationRequest('testMethod', null);

        $this->makeSingleJsonRpcRequestNotification($request);
    }

    public function testBatchRequest()
    {
        $request = array(
            json_decode($this->createSingleRequest('noParamsMethod', null, 1)),
            json_decode($this->createSingleRequest('noParamsMethod3', null, 2)),
            json_decode($this->createSingleRequest('mathMethod', null, null)),
            json_decode($this->createSingleRequest('noParamsMethod', null, 3)),
            json_decode($this->createSingleNotificationRequest('noParamsMethod', null)),
            json_decode($this->createSingleRequest('noParamsMethod', null, 'send')),
        );

        $result = $this->server->process(json_encode($request));

        $result = json_decode($result);

        $this->assertCount(5, $result);

        foreach ($result as $row) {
            switch ($row->id) {
                case 1:
                    $this->assertSingleJsonRpcRequest($row);
                    break;
                case 2:
                    $this->assertSingleJsonRpcErrorRequest($row);
                    $this->assertEquals(JsonRpcException::ERROR_METHOD_NOT_FOUND, $row->error->code);
                    break;
                case null:
                    $this->assertSingleJsonRpcErrorRequest($row);
                    $this->assertEquals(JsonRpcException::ERROR_INVALID_PARAMS, $row->error->code);
                    break;
                case 3:
                    $this->assertSingleJsonRpcRequest($row);
                    break;
                case 'send':
                    $this->assertSingleJsonRpcRequest($row);
                    break;
                default:
                    $this->fail('requests more then expected');
                    break;
            }
        }
    }

    public function testBatchRequestWithErrors()
    {
        $request = array(
            json_decode($this->createSingleRequest('noParamsMethod', null, 1)),
            array(),
            array(1),
            '',
            3
        );

        $result = $this->server->process(json_encode($request));
        $result = json_decode($result);

        $this->assertCount(5, $result);

        foreach ($result as $row) {
            switch ($row->id) {
                case 1:
                    $this->assertSingleJsonRpcRequest($row);
                    break;
                default:
                    $this->assertSingleJsonRpcErrorRequest($row);
                    $this->assertEquals(JsonRpcException::ERROR_INVALID_REQUEST, $row->error->code);
                    break;
            }
        }
    }

    public function testBatchRequestWithNotificationsOnly()
    {
        $request = array(
            json_decode($this->createSingleNotificationRequest('noParamsMethod', null)),
            json_decode($this->createSingleNotificationRequest('mathMethod', null)),
            json_decode($this->createSingleNotificationRequest('noExistsMethod', null)),
            json_decode($this->createSingleNotificationRequest('noParamsMethod', null)),
            json_decode($this->createSingleNotificationRequest('noParamsMethod', null)),
        );

        $result = $this->server->process(json_encode($request));

        $this->assertEmpty($result);
    }

    public function testMakeErrorResponse()
    {
        $message = '';
        try {
            2/0;
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        $request = $this->createSingleRequest('errorMethod', null);
        $result = $this->makeSingleJsonRpcErrorRequest($request);

        $this->assertEquals(JsonRpcException::ERROR_INTERNAL_ERROR, $result->error->code);

        $this->assertEquals($message, $result->error->data);
    }



    protected function createSingleRequest($method, $params, $requestId = null)
    {
        $request = array(
            'jsonrpc'   =>  '2.0',
            'method'    =>  $method,
            'params'    =>  $params,
            'id'        =>  $requestId
        );
        return json_encode($request);
    }

    protected function createSingleNotificationRequest($method, $params)
    {
        $request = array(
            'jsonrpc'   =>  '2.0',
            'method'    =>  $method,
            'params'    =>  $params
        );
        return json_encode($request);
    }

    protected function makeSingleJsonRpcRequest($data)
    {

        $str = $this->server->process($data);

        $result = json_decode($str);

        $this->assertSingleJsonRpcRequest($result);

        return $result;
    }

    protected function assertSingleJsonRpcRequest($data)
    {
        $this->assertObjectHasAttribute('jsonrpc', $data);
        $this->assertObjectHasAttribute('result', $data);
        $this->assertObjectHasAttribute('id', $data);
        $this->assertObjectNotHasAttribute('error', $data);
    }

    protected function makeSingleJsonRpcRequestNotification($data)
    {

        $str = $this->server->process($data);

        $this->assertEmpty($str);
    }

    protected function makeSingleJsonRpcErrorRequest($data)
    {

        $str = $this->server->process($data);

        $result = json_decode($str);

        $this->assertSingleJsonRpcErrorRequest($result);


        return $result;
    }

    protected function assertSingleJsonRpcErrorRequest($data)
    {
        $this->assertObjectHasAttribute('jsonrpc', $data);
        $this->assertObjectHasAttribute('id', $data);
        $this->assertObjectHasAttribute('error', $data);
        $this->assertObjectHasAttribute('code', $data->error);
        $this->assertObjectHasAttribute('message', $data->error);
        $this->assertObjectNotHasAttribute('result', $data);
    }


    protected function setUp ()
    {
        if (empty($this->server)) {
            $this->server = new JsonRpcServer(new BaseJsonRpcSerializer());
            $this->server->addService(
                JsonRpcServer::DEFAULT_NAMESPACE,
                '\\AgentSIB\\JsonRpc\\Tests\\Services\\FirstJsonRpcService'
            );
            $this->server->addService(
                'second',
                '\\AgentSIB\\JsonRpc\\Tests\\Services\\SecondJsonRpcService'
            );
        }
    }
}
