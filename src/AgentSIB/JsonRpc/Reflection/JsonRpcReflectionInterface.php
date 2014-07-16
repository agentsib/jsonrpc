<?php

namespace AgentSIB\JsonRpc\Reflection;


interface JsonRpcReflectionInterface {

    public function init($class, $method);

    public function invokeMethod($params = null);

} 