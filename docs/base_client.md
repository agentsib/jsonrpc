Base client
===========

For example:

```php
<?php

use \AgentSIB\JsonRpc\JsonRpcClient;
use \AgentSIB\JsonRpc\Transports\CurlJsonRpcTransport;

$client = new JsonRpcClient(new CurlJsonRpcTransport('http://example.org/jsonrpc'));

try {
    $response = $client->makeSingleRequest('user.testMethod', array('param1' => 'test'));
    
    if ($response->isCorrect()) {
        if ($response->isSuccess()) {
            var_dump($response->getResult());
        } else {
            die($response->getErrorCode(). ': ' .$response->getErrorMessage());
        }
    }
} catch (\Exception $e) {
    // Some curl exception
}
```

You can use single requests:

```php
$response = $client->makeSingleRequest('user.testMethod', array('param1' => 'test'));
```

Or notifications:

```php
$client->makeSingleNotification('user.notifyMessage', array('message'=>'test'));
```

Or batch request:

```php
$response = $client->makeBatchRequest(
    BatchJsonRpcRequest::create()
        ->addRequest(new SingleJsonRpcRequest('user.testMethod', array('param1' => 'test'), 'userinfo'))
        ->addRequest(new NotificationJsonRpcRequest('user.sendMessage', array('message' => 'test2')))
        ->addRequest(new SingleJsonRpcRequest('user.notExistsMethod', null, 'notexist'))
);

if ($response->isCorrect()) {
    echo count($response->getAllResponses()); // 2, because notifications not in response
    echo $response->getRawResponse(); // Input json
    if ($response->getResponseById('userinfo')->isSuccess()){
        var_dump($response->getResponseById('userinfo')->getResult());
    }

    // or

    foreach ($response->getAllResponses() AS $resp) {
        echo $resp->getId();
    }
}
```