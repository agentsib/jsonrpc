JSON-RPC Library
================

[![Build Status](https://travis-ci.org/agentsib/jsonrpc.svg?branch=master)](https://travis-ci.org/agentsib/jsonrpc)
[![Coverage Status](https://coveralls.io/repos/agentsib/jsonrpc/badge.png?branch=master)](https://coveralls.io/r/agentsib/jsonrpc?branch=master)

[![Latest Stable Version](https://poser.pugx.org/agentsib/jsonrpc/v/stable.svg)](https://packagist.org/packages/agentsib/jsonrpc) 
[![Latest Unstable Version](https://poser.pugx.org/agentsib/jsonrpc/v/unstable.svg)](https://packagist.org/packages/agentsib/jsonrpc) 
[![Total Downloads](https://poser.pugx.org/agentsib/jsonrpc/downloads.svg)](https://packagist.org/packages/agentsib/jsonrpc) 

**WARNING!** Library is still in development. The structure of the project may change at any time.

[Documentation](docs/index.md)

Roadmap
-------

###Server###
* Versions support for api
* Cache reflection operations
* Autocreate documentation (may be SMD?)
* PHPDoc 

Features
--------

Client and server fully satisfy the specifications [JSON-RPC 2.0](http://www.jsonrpc.org/specification).

###Server###

* Easy class based creating api
* Namespaces
* Batch requests
* Notifications
* Customize serialization and deserialization

###Client###

* Easy request api
* Custom transports (Curl, Internal or own)
* Batch requests
* Notifications
