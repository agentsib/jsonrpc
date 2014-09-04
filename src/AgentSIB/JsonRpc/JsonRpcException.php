<?php


namespace AgentSIB\JsonRpc;


use Exception;

class JsonRpcException extends \Exception
{

    const ERROR_PARSE_ERROR         = -32700;
    const ERROR_INVALID_REQUEST     = -32600;
    const ERROR_METHOD_NOT_FOUND    = -32601;
    const ERROR_INVALID_PARAMS      = -32602;
    const ERROR_INTERNAL_ERROR      = -32603;

    protected $errorsMessages = array(
        self::ERROR_PARSE_ERROR         => 'Parse error',
        self::ERROR_INVALID_REQUEST     => 'Invalid Request',
        self::ERROR_METHOD_NOT_FOUND    => 'Method not found',
        self::ERROR_INVALID_PARAMS      => 'Invalid params',
        self::ERROR_INTERNAL_ERROR      => 'Internal error',
    );

    protected $data;

    public function __construct ($code = 0, Exception $previous = null)
    {
        $message = isset($this->errorsMessages[$code]) ?
                   $this->errorsMessages[$code] :
                   $this->errorsMessages[self::ERROR_INTERNAL_ERROR];
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get data for exception
     * @return string
     */
    public function getData ()
    {
        return $this->data;
    }

    /**
     * Set data for exception
     * @param string $data
     */
    public function setData ($data)
    {
        $this->data = $data;
    }
}
