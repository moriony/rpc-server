<?php

namespace Moriony\RpcServer\Server;

final class Events
{
    const EVENT_HTTP_REQUEST = 'rpc_server.http_request';
    const EVENT_RPC_REQUEST = 'rpc_server.json_rpc_request';
    const EVENT_RPC_RESPONSE = 'rpc_server.json_rpc_response';
    const EVENT_RPC_SERVICE = 'rpc_server.service';
    const EVENT_RPC_METHOD_CALL = 'rpc_server.method_call';
    const EVENT_RPC_EXCEPTION = 'rpc_server.exception';
    const EVENT_RPC_CREATE_RESPONSE = 'rpc_server.create_response';
}
