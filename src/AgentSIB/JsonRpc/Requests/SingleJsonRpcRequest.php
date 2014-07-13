<?php


namespace AgentSIB\JsonRpc\Requests;

class SingleJsonRpcRequest extends NotificationJsonRpcRequest
{
    private $id;

    public function __construct ($method, $params = null, $requestId = null)
    {
        parent::__construct($method, $params);
        $this->id = $requestId;
    }

    public function getId ()
    {
        return $this->id;
    }

    public function serializeToArray ()
    {
        $result = parent::serializeToArray();
        $result['id'] = $this->id;

        return $result;
    }
}
