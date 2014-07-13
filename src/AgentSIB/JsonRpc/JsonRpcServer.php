<?php


namespace AgentSIB\JsonRpc;


class JsonRpcServer
{

    const JSON_RPC_VERSION = '2.0';

    const DEFAULT_NAMESPACE = '';

    private $services = array();

    /** @var  JsonRpcSerializerInterface */
    private $serializer;

    public function __construct (JsonRpcSerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function addService($namespace, $class)
    {
        $namespaceNormalize = strtolower($namespace);

        if (!preg_match('/^[a-z]+$/', $namespaceNormalize) && $namespaceNormalize != self::DEFAULT_NAMESPACE) {
            throw new \Exception('Uncorrect namespace name');
        }

        if (isset($this->services[$namespaceNormalize])) {
            throw new \Exception('Namespace already exists');
        }

        if (!class_exists($class)) {
            throw new \Exception('JsonRPC class not exists');
        }

        $this->services[$namespace] = $class;
    }

    public function process($request)
    {
        $output = null;
        try {

            $data = $this->serializer->parseRequest($request);

            if (is_null($data) || empty($data)) {
                throw new JsonRpcException(JsonRpcException::ERROR_PARSE_ERROR);
            }

            /** @var array $batch */
            if (($batch = $this->interpretBatch($data)) !== false) {
                $result = array();
                foreach ($batch as $call) {
                    $r = $this->processCall($call);
                    if (!is_null($r)) {
                        array_push($result, $r);
                    }
                }
                $output = $result;
            } else {
                $output = $this->processCall($data);
            }
        } catch (JsonRpcException $e) {
            $output = $this->makeErrorResponse($e);
        }

        return $this->serializer->serializeResponse($output);

    }

    protected function processCall($call)
    {
        $isNotification = is_object($call) && !property_exists($call, 'id');

        try {
            $this->validateCall($call);

            $pos = strpos($call->method, '.');
            if ($pos !== false) {
                $namespace = trim(strtolower(substr($call->method, 0, $pos)));
                $method = trim(strtolower(substr($call->method, $pos + 1)));

                if (empty($namespace) || empty($method)) {
                    throw new JsonRpcException(JsonRpcException::ERROR_METHOD_NOT_FOUND);
                }
            } else {
                $namespace = self::DEFAULT_NAMESPACE;
                $method = trim(strtolower($call->method));

                if (empty($method)) {
                    // @codeCoverageIgnoreStart
                    throw new JsonRpcException(JsonRpcException::ERROR_INVALID_REQUEST);
                    // @codeCoverageIgnoreEnd
                }
            }

            if (!isset($this->services[$namespace])) {
                // @codeCoverageIgnoreStart
                throw new JsonRpcException(JsonRpcException::ERROR_METHOD_NOT_FOUND);
                // @codeCoverageIgnoreEnd
            }

            $refClass = new \ReflectionClass($this->services[$namespace]);

            if (!$refClass->hasMethod($method)) {
                throw new JsonRpcException(JsonRpcException::ERROR_METHOD_NOT_FOUND);
            }

            $refMethod = $refClass->getMethod($method);

            if (!$refMethod->isPublic() || $refMethod->isStatic()) {
                throw new JsonRpcException(JsonRpcException::ERROR_METHOD_NOT_FOUND);
            }

            switch (gettype($call->params)) {
                case 'array': // Old style
                    if ($refMethod->getNumberOfRequiredParameters() > count($call->params)) {
                        throw new JsonRpcException(JsonRpcException::ERROR_INVALID_PARAMS);
                    }
                    return $this->makeResultResponse(
                        $refMethod->invokeArgs(
                            $refClass->newInstance(),
                            $call->params
                        ),
                        isset($call->id)?$call->id:null,
                        $isNotification
                    );
                    break;
                case 'object': // Standard style
                    $params = array();
                    foreach ($refMethod->getParameters() as $refParam) {
                        if (property_exists($call->params, $refParam->getName())) {
                            $params[$refParam->getName()] = $call->params->{$refParam->getName()};
                        } else {
                            if ($refParam->isDefaultValueAvailable()) {
                                $params[$refParam->getName()] = $refParam->getDefaultValue();
                            } else {
                                throw new JsonRpcException(JsonRpcException::ERROR_INVALID_PARAMS);
                            }
                        }
                    }
                    return $this->makeResultResponse(
                        $refMethod->invokeArgs(
                            $refClass->newInstance(),
                            $params
                        ),
                        isset($call->id)?$call->id:null,
                        $isNotification
                    );
                    break;
                default: // For example null
                    if ($refMethod->getNumberOfRequiredParameters()) {
                        throw new JsonRpcException(JsonRpcException::ERROR_INVALID_PARAMS);
                    }
                    return $this->makeResultResponse(
                        $refMethod->invoke(
                            $refClass->newInstance()
                        ),
                        isset($call->id)?$call->id:null,
                        $isNotification
                    );
                    break;
            }

        } catch (\Exception $e) {
            return $this->makeErrorResponse(
                $e,
                isset($call->id)?$call->id:null,
                is_object($call) && !property_exists($call, 'id')
            );
        }

    }

    protected function makeResultResponse($data, $callId = null, $isNotification = false)
    {
        if ($isNotification) {
            return null;
        }

        $response = array(
            'jsonrpc'   =>  self::JSON_RPC_VERSION,
            'result'    =>  $data,
            'id'        =>  $callId
        );

        return $response;
    }

    protected function makeErrorResponse(\Exception $exception, $callId = null, $isNotification = false)
    {
        if ($isNotification) {
            return null;
        }

        if ($exception instanceof JsonRpcException) {
            $jException  = $exception;
        } else {
            $jException  = new JsonRpcException(JsonRpcException::ERROR_INTERNAL_ERROR, $exception);
            $jException ->setData($exception->getMessage());

        }

        $response = array(
            'jsonrpc'   =>  self::JSON_RPC_VERSION,
            'error'     =>  array(
                'code'  =>  $jException ->getCode(),
                'message' => $jException ->getMessage(),
            ),
            'id'        =>  $callId
        );

        if ($jException ->getData()) {
            $response['error']['data'] = $jException ->getData();
        }

        return $response;
    }

    protected function validateCall($call)
    {
        if (!is_object($call)) {
            throw new JsonRpcException(JsonRpcException::ERROR_INVALID_REQUEST);
        }

        if (!property_exists($call, 'jsonrpc')) {
            throw new JsonRpcException(JsonRpcException::ERROR_INVALID_REQUEST);
        }

        if ($call->jsonrpc != self::JSON_RPC_VERSION) {
            throw new JsonRpcException(JsonRpcException::ERROR_INVALID_REQUEST);
        }

        if (!property_exists($call, 'method') || !property_exists($call, 'params')) {
            throw new JsonRpcException(JsonRpcException::ERROR_INVALID_REQUEST);
        }


        $paramsType = gettype($call->params);
        if (!is_null($call->params) && $paramsType != 'object' && $paramsType != 'array') {
            throw new JsonRpcException(JsonRpcException::ERROR_INVALID_PARAMS);
        }
    }

    protected function interpretBatch($data)
    {
        if (!is_array($data) || count($data) == 0) {
            return false;
        }

        return $data;
    }
}
