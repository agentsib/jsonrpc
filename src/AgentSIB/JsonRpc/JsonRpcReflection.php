<?php


namespace AgentSIB\JsonRpc;


class JsonRpcReflection {

    /** @var  \ReflectionClass */
    private $class;

    /** @var  \ReflectionMethod */
    private $method;

    public function __construct ($class, $method)
    {
        if (!class_exists($class)) {
            throw new JsonRpcException(JsonRpcException::ERROR_METHOD_NOT_FOUND);
        }

        /*if (!($class instanceof JsonRpcServiceInterface)) {
            throw new JsonRpcException(JsonRpcException::ERROR_METHOD_NOT_FOUND);
        }*/

        $this->class = new \ReflectionClass($class);

        if (!$this->class->hasMethod($method)) {
            throw new JsonRpcException(JsonRpcException::ERROR_METHOD_NOT_FOUND);
        }

        $this->method = $this->class->getMethod($method);

        if (!$this->method->isPublic() || $this->method->isStatic() || $this->method->isAbstract()) {
            throw new JsonRpcException(JsonRpcException::ERROR_METHOD_NOT_FOUND);
        }
    }


    public function getNumberOfRequiredParameters()
    {
        return $this->method->getNumberOfRequiredParameters();
    }

    public function getParameters()
    {
        return $this->method->getParameters();
    }

    public function invoke($params = null)
    {
        if ($params == null) {
            $params = array();
        }
        return $this->method->invokeArgs($this->class->newInstance(), $params);
    }

    public function prepareParameters($params = array()) {
        $result = array();
        foreach ($this->method->getParameters() as $refParam) {
            if (property_exists($params, $refParam->getName())) {
                $result[$refParam->getName()] = $params->{$refParam->getName()};
            } else {
                if ($refParam->isDefaultValueAvailable()) {
                    $result[$refParam->getName()] = $refParam->getDefaultValue();
                } else {
                    throw new JsonRpcException(JsonRpcException::ERROR_INVALID_PARAMS);
                }
            }
        }
        return $result;
    }
} 