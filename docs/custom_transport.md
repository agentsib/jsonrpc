Custom transport
================

You can custom transport class if you want. For example class `AgentSIB\JsonRpc\Transports\InternalJsonRpcTransport` used for internal requests in tests:

```php
    <?php
    
    namespace AgentSIB\JsonRpc\Transports;
    
    use AgentSIB\JsonRpc\JsonRpcServer;
    
    class InternalJsonRpcTransport implements JsonRpcTransportInterface
    {
        private $server;
    
        public function __construct (JsonRpcServer $server)
        {
            $this->server = $server;
        }
    
        public function sendRequest ($request)
        {
            return $this->server->process($request);
        }
    }
```

Usage:

```php
    $server = new JsonRpcServer(new BaseJsonRpcSerializer());
    $server->addService(JsonRpcServer::DEFAULT_NAMESPACE, '\\AgentSIB\\JsonRpc\\Services\\FirstJsonRpcService');
    $client = new JsonRpcClient(new InternalJsonRpcTransport($server));
```

Now you can test you server.