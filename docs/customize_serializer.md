Customize serializer
====================

You can customize serializer for change input and output format. For example:

```php
    <?php
    
    namespace AgentSIB\JsonRpcBundle\Server;
    
    use AgentSIB\JsonRpc\JsonRpcException;
    use AgentSIB\JsonRpc\JsonRpcSerializerInterface;
    use Symfony\Component\DependencyInjection\ContainerInterface;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Request;
    
    class SymfonyJsonRpcSerializer implements JsonRpcSerializerInterface
    {
        /** @var  ContainerInterface */
        private $container;
    
        function __construct (ContainerInterface $container)
        {
            $this->container;
        }
    
        /**
         * @inheritdoc
         */
        public function parseRequest ($request)
        {
            if ($request instanceof Request) {
                return @json_decode($request->getContent(), false, 32);
            }
            throw new JsonRpcException(JsonRpcException::ERROR_PARSE_ERROR);
        }
    
        /**
         * @inheritdoc
         */
        public function serializeResponse ($response)
        {
            return new JsonResponse($response);
        }
    }
```

As well, you can use additional checks. For example:

```php
    // ...
    public function parseRequest ($request)
    {
        if (!$this->checkSignature($request->getContent())) {
            throw new JsonRpcException(JsonRpcException::ERROR_INVALID_REQUEST);
        }
        if ($request instanceof Request) {
            return @json_decode($request->getContent(), false, 32);
        }
        throw new JsonRpcException(JsonRpcException::ERROR_PARSE_ERROR);
    }
    // ...
```

And something more...