<?php


namespace AgentSIB\JsonRpc\Transports;

/**
 * @codeCoverageIgnore
 */
class CurlJsonRpcTransport implements JsonRpcTransportInterface
{

    protected $curl_opts = array(
        CURLOPT_HEADER         => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => array('Content-type: application/json', 'Accept: application/json')
    );

    private $url;

    public function __construct ($url)
    {
        $this->url = $url;
    }


    public function sendRequest ($request)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array_merge($this->curl_opts, array(
                CURLOPT_URL        => $this->url,
                CURLOPT_POSTFIELDS => $request,
        )));

        $response   = curl_exec($curl);
        $error_code = curl_errno($curl);
        $error_msg  = curl_error($curl);
        $response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if (!$error_code) {
            throw new \Exception('Transport error #' . $error_code.': ' . $error_msg);
        }

        if ($response_code != '200') {
            throw new \Exception('Http error code: ' . $response_code);
        }

        return $response;
    }
}
