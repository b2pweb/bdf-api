<?php

$content = json_decode(file_get_contents('php://input'), true);
header('Content-Type: application/json-rpc');

if (!is_array($content)) {
    exit(json_encode([
        'jsonrpc' => '2.0',
        'error' => [
            'code' => -32700,
            'message' => 'Parse error',
        ],
        'id' => null,
    ]));
}

if (!isset($content['jsonrpc'], $content['id'], $content['method']) || $content['jsonrpc'] !== '2.0') {
    exit(json_encode([
        'jsonrpc' => '2.0',
        'error' => [
            'code' => -32600,
            'message' => 'Invalid Request'
        ],
        'id' => $content['id'] ?? null,
    ]));
}

switch ($content['method']) {
    case 'echo':
        exit(json_encode([
            'jsonrpc' => '2.0',
            'result' => $content['params'],
            'id' => $content['id'],
        ]));

    default:
        exit(json_encode([
            'jsonrpc' => '2.0',
            'error' => [
                'code' => -32601,
                'message' => 'Method not found'
            ],
            'id' => $content['id'],
        ]));
}
