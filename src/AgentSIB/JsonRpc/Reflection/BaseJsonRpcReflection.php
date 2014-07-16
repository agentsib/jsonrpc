<?php


namespace AgentSIB\JsonRpc\Reflection;


use AgentSIB\JsonRpc\JsonRpcException;
use AgentSIB\JsonRpc\JsonRpcServiceInterface;

class BaseJsonRpcReflection implements JsonRpcReflectionInterface{

    /** @var  \ReflectionClass */
    private $class;

    /** @var  \ReflectionMethod */
    private $method;

    public function init ($class, $method)
    {
        if (!class_exists($class)) {
            throw new JsonRpcException(JsonRpcException::ERROR_METHOD_NOT_FOUND);
        }

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

    public function invokeMethod($params = null)
    {
        $preparedParams = $this->prepareParameters($params);
        if ($preparedParams == null) {
            $preparedParams = array();
        }
        return $this->method->invokeArgs($this->getClassInstance(), $preparedParams);
    }

    public function getClassInstance() {

        $class = $this->class->newInstance();
        if (!($class instanceof JsonRpcServiceInterface)) {
            throw new JsonRpcException(JsonRpcException::ERROR_METHOD_NOT_FOUND);
        }

        return $class;
    }

    public function prepareParameters($params = array()) {
        switch (gettype($params)) {
            case 'array':
                if ($this->getNumberOfRequiredParameters() > count($params)) {
                    throw new JsonRpcException(JsonRpcException::ERROR_INVALID_PARAMS);
                }
                return $params;
            case 'object':
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
            default:
                if ($this->getNumberOfRequiredParameters()) {
                    throw new JsonRpcException(JsonRpcException::ERROR_INVALID_PARAMS);
                }
                return null;

        }
    }

}