<?php


namespace AgentSIB\JsonRpc\Requests;


class BatchJsonRpcRequest extends JsonRpcRequest
{
    private $requests;
    private $keys;

    public function __construct ()
    {
        $this->requests = array();
        $this->keys = array();
    }

    public static function create()
    {
        return new self;
    }

    public function addRequest(JsonRpcRequest $request)
    {
        $key = $this->genUniqKey();

        if ($request instanceof SingleJsonRpcRequest) {

            $requestId = $request->getId();

            if (!(is_string($requestId) || is_numeric($requestId))) {
                throw new \Exception('ID must be string or numeric');
            }

            if (in_array($requestId, $this->keys)) {
                throw new \Exception('Request with ID '.$requestId.' already exists');
            }

            $this->keys[$key] = $requestId;
            $this->requests[$key] = $request;

        } elseif ($request instanceof NotificationJsonRpcRequest) {

            $this->requests[$key] = $request;

        } else {
            throw new \Exception('Unexpected request type');
        }

        return $this;
    }

    public function getRequestById($requestId)
    {
        $key = array_search($requestId, $this->keys);

        if ($key) {
            return $this->requests[$key];
        }
        return null;
    }

    private function genUniqKey()
    {
        do {
            $key = uniqid('', true);
        } while (array_key_exists($key, $this->requests));

        return $key;
    }

    public function serializeToArray ()
    {
        if (empty($this->requests)) {
            throw new \Exception('No request to serialize');
        }

        $result = array();

        /** @var JsonRpcRequest $request */
        foreach ($this->requests as $request) {
            array_push($result, $request->serializeToArray());
        }

        return $result;
    }
}
