Base Server
===========

Simple code:

```php
<?php
// index.php

use \AgentSIB\JsonRpc\JsonRpcServer;
use \AgentSIB\JsonRpc\Serializers\BaseJsonRpcSerializer;

$server = new JsonRpcServer(new BaseJsonRpcSerializer());
$server->addService(JsonRpcServer::DEFAULT_NAMESPACE, '\JsonRpc\MyService');

echo $server->process(file_get_contents('php://input'));
```

```php
<?php
// JsonRpc/MyService.php
namespace JsonRpc;

use AgentSIB\JsonRpc\JsonRpcServiceInterface;

class MyService implements JsonRpcServiceInterface
{
    public function testMethod() {
        return 'something';
    }
}
```

Now you can send request: `{"jsonrpc":"2.0", "method":"testMethod", "params":null, "id":"null"}`.

Use namespaces:

```php
// ...
$server->addService(JsonRpcServer::DEFAULT_NAMESPACE, '\JsonRpc\MyService');
$server->addService('users', '\JsonRpc\MyService2');
$server->addService('forum', '\JsonRpc\MyService3');
// ...
```

Example requests:

* `{"jsonrpc":"2.0", "method":"testMethod", "params":null, "id":"null"}`
* `{"jsonrpc":"2.0", "method":"users.getUser", "params":{"id":123}, "id":"null"}`
* `{"jsonrpc":"2.0", "method":"forum.refreshData", "params":{"parentId":null}}`

Simple, right?)