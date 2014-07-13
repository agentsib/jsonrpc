<?php


namespace AgentSIB\JsonRpc\Response;


class BatchJsonRpcResponse extends JsonRpcResponse
{

    private $responses;

    public function __construct ($response)
    {
        $this->responses = array();
        parent::__construct($response);
    }


    protected function parseResponse ($response)
    {
        if (!is_array($response)) {
            return false;
        }

        foreach ($response as $row) {
            $item = new SingleJsonRpcResponse(json_encode($row));
            if (!$item->isCorrect()) {
                $this->responses = array();
                return false;
            }
            $this->responses['id_'.$item->getId()] = $item;
        }
        return true;
    }

    /**
     * @param $id
     * @return SingleJsonRpcResponse
     * @throws \Exception
     */
    public function getResponseById($responseId)
    {
        if (!$this->hasResponseId($responseId)) {
            throw new \Exception('Response not exists');
        }
        return $this->responses['id_'.$responseId];
    }

    public function hasResponseId($responseId)
    {
        return array_key_exists('id_'.$responseId, $this->responses);
    }

    public function getAllResponses()
    {
        return count($this->responses)?array_values($this->responses):null;
    }
}
