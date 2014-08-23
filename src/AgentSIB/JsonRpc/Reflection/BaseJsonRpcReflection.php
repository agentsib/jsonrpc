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
            /**
             * @codeCoverageIgnore
             */
            return false;
        }

        $this->class = new \ReflectionClass($class);

        if (!$this->getClass()->isSubclassOf('AgentSIB\\JsonRpc\\JsonRpcServiceInterface')) {
            return false;
        }

        if (!$this->getClass()->hasMethod($method)) {
            return false;
        }

        $this->method = $this->getClass()->getMethod($method);

        if (!$this->getMethod()->isPublic() || $this->getMethod()->isStatic() || $this->getMethod()->isAbstract()) {
            return false;
        }

        return true;
    }


    public function getNumberOfRequiredParameters()
    {
        return $this->getMethod()->getNumberOfRequiredParameters();
    }

    public function invokeMethod($params = null)
    {
        $preparedParams = $this->prepareParameters($params);
        if ($preparedParams == null) {
            $preparedParams = array();
        }
        return $this->getMethod()->invokeArgs($this->getClassInstance(), $preparedParams);
    }

    public function getClassInstance() {

        $class = $this->getClass()->newInstance();

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
                foreach ($this->getMethod()->getParameters() as $refParam) {
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

    /**
     * @return \ReflectionMethod
     */
    protected function getMethod()
    {
        return $this->method;
    }

    /**
     * @return \ReflectionClass
     */
    protected function getClass()
    {
        return $this->class;
    }

}